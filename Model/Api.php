<?php
declare(strict_types=1);
/**
 * Copyright © ict. All rights reserved.
 * https://ict.lv/
 */

namespace ICT\Klar\Model;

use Exception;
use ICT\Klar\Api\Data\ApiInterface;
use ICT\Klar\Helper\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Api implements ApiInterface
{
    private Curl $curl;
    private Config $config;
    private PsrLoggerInterface $logger;
    private ApiRequestParamsBuilder $paramsBuilder;
    private string $requestData;
    private Json $jsonSerializer;
    private OrderCollectionFactory $orderCollectionFactory;

    /**
     * Api constructor.
     *
     * @param Curl $curl
     * @param Config $config
     * @param PsrLoggerInterface $logger
     * @param ApiRequestParamsBuilder $paramsBuilder
     * @param Json $jsonSerializer
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        Curl $curl,
        Config $config,
        PsrLoggerInterface $logger,
        ApiRequestParamsBuilder $paramsBuilder,
        Json $jsonSerializer,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->requestData = '';
        $this->curl = $curl;
        $this->config = $config;
        $this->logger = $logger;
        $this->paramsBuilder = $paramsBuilder;
        $this->jsonSerializer = $jsonSerializer;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Get orders by order IDs.
     *
     * @param int[] $orderIds
     *
     * @return null|SalesOrderInterface[]
     * @throws NoSuchEntityException
     */
    private function getOrders(array $orderIds): ?array
    {
        if ($this->config->getIsEnabled()) {
            $items = $this->orderCollectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $orderIds])
                ->getItems();

            if (count($items) !== count($orderIds)) {
                throw new NoSuchEntityException(
                    __('Could not find orders with ids: `%ids`',
                        [
                            'ids' => implode(', ', array_diff(array_keys($items), $orderIds))
                        ]
                    )
                );
            }
            return $items;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function validateAndSend(array $ids): int
    {
        $result = 0;
        $salesOrders = $this->getOrders($ids);

        if ($salesOrders) {
            $this->setRequestData($salesOrders);
        } else {
            return $result;
        }

        if ($this->validate($salesOrders)) {
            $result = $this->json($salesOrders);
        } elseif (count($ids) > 1) {
            foreach ($ids as $id) {
                $result += $this->validateAndSend([$id]);
            }
        }

        return $result;
    }

    public function getJsonDataForOrders(array $ids): string
    {
        $salesOrders = $this->getOrders($ids);

        if ($salesOrders) {
            $this->setRequestData($salesOrders);
            return $this->requestData;
        } else {
            return __('Could not get order data');
        }
    }

    /**
     * Make order validate request.
     *
     * @param SalesOrderInterface[] $salesOrders
     *
     * @return bool
     */
    private function validate(array $salesOrders): bool
    {
        $orderIds = implode(', ', array_keys($salesOrders));
        $this->logger->info(__('Validating orders "#%1".', $orderIds));

        if (count($salesOrders) > self::BATCH_SIZE) {
            $this->logger->info(
                __('Batch size must be less or equal %1, %2 provided.', self::BATCH_SIZE, count($salesOrders))
            );
            return false;
        }

        $this->getCurlClient()->post(
            $this->getRequestUrl(self::ORDERS_VALIDATE_PATH, true),
            $this->requestData
        );

        if ($this->getCurlClient()->getStatus() === self::STATUS_OK || $this->getCurlClient()->getStatus() === self::STATUS_CREATED) {
            return $this->handleSuccess($orderIds);
        }

        if ($this->getCurlClient()->getStatus() === self::STATUS_BAD_REQUEST) {
            $this->logger->info(__('Failed to validate orders "#%1".', $orderIds));
            return $this->handleError($orderIds);
        }

        return true;
    }

    /**
     * Set request data.
     *
     * @param SalesOrderInterface[] $salesOrders
     *
     * @return void
     */
    private function setRequestData(array $salesOrders): void
    {
        try {
            $items = [];
            foreach ($salesOrders as $salesOrder) {
                $items[] = $this->paramsBuilder->buildFromSalesOrder($salesOrder);
            }

            $this->requestData = $this->jsonSerializer->serialize($items);
        } catch (Exception $e) {
            $this->logger->info(__('Error building params: %1', $e->getMessage()));
        }
    }

    /**
     * Get CURL client.
     *
     * @return Curl
     */
    private function getCurlClient(): Curl
    {
        $this->curl->setHeaders($this->getHeaders());

        return $this->curl;
    }

    /**
     * Get request headers.
     *
     * @return string[]
     */
    private function getHeaders(): array
    {
        return [
            'Expect' => '',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getApiToken(),
        ];
    }

    /**
     * Get API token.
     *
     * @return string
     */
    private function getApiToken(): string
    {
        return $this->config->getApiToken();
    }

    /**
     * Get request endpoint URL.
     *
     * @param string $path
     * @param bool $includeVersion
     *
     * @return string
     */
    private function getRequestUrl(string $path, bool $includeVersion = false): string
    {
        if ($includeVersion) {
            $baseUrl = $this->config->getApiUrl();
            $version = $this->config->getApiVersion();

            return rtrim($baseUrl, "/") . '/' . $version . $path;
        }

        return $this->config->getApiUrl() . $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus(): array
    {
        if ($this->config->getIsEnabled()) {
            return $this->status();
        }

        return [];
    }

    /**
     * Make order status API request.
     *
     * @return array
     */
    private function status(): array
    {
        $this->getCurlClient()->get($this->getRequestUrl(self::ORDERS_STATUS_PATH));

        if ($this->getCurlClient()->getStatus() === 200) {
            try {
                return json_decode(
                    $this->getCurlClient()->getBody(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (Exception $e) {
                $this->logger->error(__('Orders status error: %1', $e->getMessage()));
            }
        }

        return [__('Error fetching orders status.')];
    }

    /**
     * Handle success.
     *
     * @param string $orderIds
     *
     * @return bool
     */
    private function handleSuccess(string $orderIds): bool
    {
        $body = $this->getCurlBody();

        if (isset($body['status']) && $body['status'] === self::ORDER_STATUS_VALID) {
            $this->logger->info(__('Orders "#%1" is valid and can be sent to Klar.', $orderIds));

            return true;
        }

        return false;
    }

    /**
     * Get curl request response body.
     *
     * @return array
     */
    private function getCurlBody(): array
    {
        try {
            return json_decode(
                $this->getCurlClient()->getBody(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Exception $e) {
            $this->logger->info(__('Error getting body from request response: %1', $e->getMessage()));
        }

        return [];
    }

    /**
     * Handle error.
     *
     * @param string $orderIds
     *
     * @return bool
     */
    private function handleError(string $orderIds): bool
    {
        $body = $this->getCurlBody();

        if (isset($body['status'], $body['errors']) && $body['status'] === self::ORDER_STATUS_INVALID) {
            foreach ($body['errors'] as $errorMessage) {
                $this->logger->info($errorMessage);
            }

            $this->logger->info(__('Failed to validate orders "#%1":', $orderIds));

            return false;
        }

        return false;
    }

    /**
     * Make order json request.
     *
     * @param SalesOrderInterface[] $salesOrders
     *
     * @return int
     */
    private function json(array $salesOrders): int
    {
        $result = 0;
        $orderIds = implode(', ', array_keys($salesOrders));
        $this->logger->info(__('Sending orders "#%1".', $orderIds));

        $this->getCurlClient()->post(
            $this->getRequestUrl(self::ORDERS_JSON_PATH, true),
            $this->requestData
        );

        if ($this->getCurlClient()->getStatus() === self::STATUS_OK) {
            $this->logger->info(__('Orders "#%1" successfully sent to Klar.', $orderIds));
            $result = count($salesOrders);
        } else {
            $this->logger->info(__('Failed to send orders "#%1".', $orderIds));
        }

        return $result;
    }
}
