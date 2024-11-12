<?php
declare(strict_types=1);
/**
 * Copyright © ict. All rights reserved.
 * https://ict.lv/
 */

namespace ICT\Klar\Model\Builders;

use ICT\Klar\Api\Data\CustomerInterface;
use ICT\Klar\Api\Data\CustomerInterfaceFactory;
use ICT\Klar\Helper\Config;
use ICT\Klar\Model\AbstractApiRequestParamsBuilder;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;

class CustomerBuilder extends AbstractApiRequestParamsBuilder
{
    private CustomerInterfaceFactory $customerFactory;
    private EncryptorInterface $encryptor;


    private Config $config;
    /**
     * CustomerBuilder builder.
     *
     * @param DateTimeFactory $dateTimeFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param EncryptorInterface $encryptor
     * @param Config $config
     *
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        CustomerInterfaceFactory $customerFactory,
        EncryptorInterface $encryptor,
        Config $config,
    ) {
        parent::__construct($dateTimeFactory);
        $this->customerFactory = $customerFactory;
        $this->encryptor = $encryptor;
        $this->config = $config;
    }   

    /**
     * Build customer from sales order.
     *
     * @param SalesOrderInterface $salesOrder
     *
     * @return array
     */
    public function buildFromSalesOrder(SalesOrderInterface $salesOrder): array
    {
        $customerId = $salesOrder->getCustomerId();
        $customerEmail = $this->config->getSendEmail() ? $salesOrder->getCustomerEmail() : "redacted@getklar.com";
        $customerEmailHash = sha1($this->config->getPublicKey() . $salesOrder->getCustomerEmail());

        if (!$customerId) {
            $customerId = $this->generateGuestCustomerId($customerEmail);
        }

        /* @var CustomerInterface $customer */
        $customer = $this->customerFactory->create();

        $customer->setId((string)$customerId);
        $customer->setEmail($customerEmail);
        $customer->setEmailHash($customerEmailHash);

        // Get customer group ID from order and load group name
        $customerGroupId = $salesOrder->getCustomerGroupId();
        if ($customerGroupId) {
            // Add group name as a tag
            $customer->setTags((string)$customerGroupId);
        }

        return $this->snakeToCamel($customer->toArray());
    }

    /**
     * Generate guest customer ID as per Klar recommendation.
     *
     * @param string $customerEmail
     *
     * @return string
     */
    private function generateGuestCustomerId(string $customerEmail): string
    {
        return $this->encryptor->hash($customerEmail, Encryptor::HASH_VERSION_MD5);
    }
}
