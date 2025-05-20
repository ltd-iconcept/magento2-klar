<?php
declare(strict_types=1);
/**
 * Copyright © ict. All rights reserved.
 * https://ict.lv/
 */

namespace ICT\Klar\Model\Builders;

use ICT\Klar\Api\Data\TaxInterface;
use ICT\Klar\Api\Data\TaxInterfaceFactory;
use ICT\Klar\Api\DiscountServiceInterface;
use ICT\Klar\Model\AbstractApiRequestParamsBuilder;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Tax\Item as TaxItemResource;

class TaxesBuilder extends AbstractApiRequestParamsBuilder
{
    public const TAXABLE_ITEM_TYPE_PRODUCT = 'product';
    public const TAXABLE_ITEM_TYPE_SHIPPING = 'shipping';

    private TaxItemResource $taxItemResource;
    private TaxInterfaceFactory $taxFactory;
    private DiscountServiceInterface $discountService;

    /**
     * TaxesBuilder constructor.
     *
     * @param DateTimeFactory $dateTimeFactory
     * @param TaxItemResource $taxItemResource
     * @param TaxInterfaceFactory $taxFactory
     * @param DiscountServiceInterface $discountService
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        TaxItemResource $taxItemResource,
        TaxInterfaceFactory $taxFactory,
        DiscountServiceInterface $discountService
    ) {
        parent::__construct($dateTimeFactory);
        $this->taxItemResource = $taxItemResource;
        $this->taxFactory = $taxFactory;
        $this->discountService = $discountService;
    }

    /**
     * Get taxes from sales order by type.
     *
     * @param int $salesOrderId
     * @param OrderItemInterface|null $salesOrderItem
     * @param string $taxableItemType
     *
     * @return array
     */
    public function build(
        int $salesOrderId,
        OrderItemInterface $salesOrderItem = null,
        string $taxableItemType = self::TAXABLE_ITEM_TYPE_PRODUCT
    ): array {
        $taxes = [];
        $taxItems = $this->taxItemResource->getTaxItemsByOrderId($salesOrderId);

        foreach ($taxItems as $taxItem) {
            $taxRate = (float)($taxItem['tax_percent'] / 100);

            if ($taxItem['taxable_item_type'] === self::TAXABLE_ITEM_TYPE_PRODUCT &&
                $salesOrderItem !== null) {
                $salesOrderItemId = (int)$salesOrderItem->getId();

                if ((int)$taxItem['item_id'] !== $salesOrderItemId) {
                    continue;
                }

                $qty = $salesOrderItem->getQtyOrdered() ? $salesOrderItem->getQtyOrdered() : 1;
                $itemPrice = (float)$salesOrderItem->getPriceInclTax()
                                - ($this->discountService->getDiscountAmountFromOrderItem($salesOrderItem) / $qty);
                $taxAmount = $itemPrice - ($itemPrice / (1+ $taxRate));
            } else {
                $taxAmount = (float)$taxItem['real_amount'];
            }

            if ($taxItem['taxable_item_type'] === $taxableItemType) {
                /* @var TaxInterface $tax */
                $tax = $this->taxFactory->create();

                $tax->setTitle($taxItem['title']);
                $tax->setTaxRate($taxRate);
                $tax->setTaxAmount($taxAmount);

                $taxes[$taxableItemType][] = $this->snakeToCamel($tax->toArray());
            }
        }

        if (!empty($taxes)) {
            return $taxes[$taxableItemType];
        }

        return $taxes;
    }

    /**
     * Build taxes for Bundle based on its children taxes
     *
     * @param array $lineItem
     * @return array
     */
    public function buildBundleTaxes(array $lineItem): array
    {
        $taxes = [];

        foreach ($lineItem['bundledProducts'] as $bundledProduct) {
            foreach ($bundledProduct['taxes'] as $tax) {
                if (!isset($taxes[$tax['title']])) {
                    // Add first record for current tax
                    $taxes[$tax['title']] = $tax;
                } else {
                    // Add tax amount to the existing record
                    $taxes[$tax['title']]['taxAmount'] += $tax['taxAmount'] * $bundledProduct['quantity'];
                }
            }
        }

        return array_values($taxes);
    }
}
