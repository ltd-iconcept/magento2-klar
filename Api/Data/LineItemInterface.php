<?php
declare(strict_types=1);
/**
 * Copyright © ict. All rights reserved.
 * https://ict.lv/
 */

namespace ICT\Klar\Api\Data;

interface LineItemInterface
{
    /**
     * String constants for property names
     */
    public const ID = 'id';
    public const PRODUCT_NAME = 'product_name';
    public const PRODUCT_ID = 'product_id';
    public const PRODUCT_VARIANT_NAME = 'product_variant_name';
    public const PRODUCT_VARIANT_ID = 'product_variant_id';
    public const PRODUCT_BRAND = 'product_brand';
    public const PRODUCT_COLLECTION = 'product_collection';
    public const PRODUCT_COGS = 'product_cogs';
    public const PRODUCT_GMV = 'product_gmv';
    public const PRODUCT_SHIPPING_WEIGHT_IN_GRAMS = 'product_shipping_weight_in_grams';
    public const SKU = 'sku';
    public const QUANTITY = 'quantity';
    public const PRODUCT_TAGS = 'product_tags';
    public const TOTAL_AMOUNT_BEFORE_TAXES_AND_DISCOUNTS = 'total_amount_before_taxes_and_discounts';
    public const TOTAL_AMOUNT_AFTER_TAXES_AND_DISCOUNTS = 'total_amount_after_taxes_and_discounts';
    public const TOTAL_LOGISTICS_COSTS = 'total_logistics_costs';
    public const DISCOUNTS = 'discounts';
    public const TAXES = 'taxes';

    /**
     * Getter for Id.
     *
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * Setter for Id.
     *
     * @param string|null $id
     *
     * @return void
     */
    public function setId(?string $id): void;

    /**
     * Getter for ProductName.
     *
     * @return string|null
     */
    public function getProductName(): ?string;

    /**
     * Setter for ProductName.
     *
     * @param string|null $productName
     *
     * @return void
     */
    public function setProductName(?string $productName): void;

    /**
     * Getter for ProductId.
     *
     * @return string|null
     */
    public function getProductId(): ?string;

    /**
     * Setter for ProductId.
     *
     * @param string|null $productId
     *
     * @return void
     */
    public function setProductId(?string $productId): void;

    /**
     * Getter for ProductVariantName.
     *
     * @return string|null
     */
    public function getProductVariantName(): ?string;

    /**
     * Setter for ProductVariantName.
     *
     * @param string|null $productVariantName
     *
     * @return void
     */
    public function setProductVariantName(?string $productVariantName): void;

    /**
     * Getter for ProductVariantId.
     *
     * @return string|null
     */
    public function getProductVariantId(): ?string;

    /**
     * Setter for ProductVariantId.
     *
     * @param string|null $productVariantId
     *
     * @return void
     */
    public function setProductVariantId(?string $productVariantId): void;

    /**
     * Getter for ProductBrand.
     *
     * @return string|null
     */
    public function getProductBrand(): ?string;

    /**
     * Setter for ProductBrand.
     *
     * @param string|null $productBrand
     *
     * @return void
     */
    public function setProductBrand(?string $productBrand): void;

    /**
     * Getter for ProductCollection.
     *
     * @return string|null
     */
    public function getProductCollection(): ?string;

    /**
     * Setter for ProductCollection.
     *
     * @param string|null $productCollection
     *
     * @return void
     */
    public function setProductCollection(?string $productCollection): void;

    /**
     * Getter for ProductCogs.
     *
     * @return float|null
     */
    public function getProductCogs(): ?float;

    /**
     * Setter for ProductCogs.
     *
     * @param float|null $productCogs
     *
     * @return void
     */
    public function setProductCogs(?float $productCogs): void;

    /**
     * Getter for ProductGmv.
     *
     * @return float|null
     */
    public function getProductGmv(): ?float;

    /**
     * Setter for ProductGmv.
     *
     * @param float|null $productGmv
     *
     * @return void
     */
    public function setProductGmv(?float $productGmv): void;

    /**
     * Getter for ProductShippingWeightInGrams.
     *
     * @return float|null
     */
    public function getProductShippingWeightInGrams(): ?float;

    /**
     * Setter for ProductShippingWeightInGrams.
     *
     * @param float|null $productShippingWeightInGrams
     *
     * @return void
     */
    public function setProductShippingWeightInGrams(?float $productShippingWeightInGrams): void;

    /**
     * Getter for Sku.
     *
     * @return string|null
     */
    public function getSku(): ?string;

    /**
     * Setter for Sku.
     *
     * @param string|null $sku
     *
     * @return void
     */
    public function setSku(?string $sku): void;

    /**
     * Getter for Quantity.
     *
     * @return float|null
     */
    public function getQuantity(): ?float;

    /**
     * Setter for Quantity.
     *
     * @param float|null $quantity
     *
     * @return void
     */
    public function setQuantity(?float $quantity): void;

    /**
     * Getter for ProductTags.
     *
     * @return string|null
     */
    public function getProductTags(): ?string;

    /**
     * Setter for ProductTags.
     *
     * @param string|null $productTags
     *
     * @return void
     */
    public function setProductTags(?string $productTags): void;

    /**
     * Getter for TotalAmountBeforeTaxesAndDiscounts.
     *
     * @return float|null
     */
    public function getTotalAmountBeforeTaxesAndDiscounts(): ?float;

    /**
     * Setter for TotalAmountBeforeTaxesAndDiscounts.
     *
     * @param float|null $totalAmountBeforeTaxesAndDiscounts
     *
     * @return void
     */
    public function setTotalAmountBeforeTaxesAndDiscounts(?float $totalAmountBeforeTaxesAndDiscounts): void;

    /**
     * Getter for TotalAmountAfterTaxesAndDiscounts.
     *
     * @return float|null
     */
    public function getTotalAmountAfterTaxesAndDiscounts(): ?float;

    /**
     * Setter for TotalAmountAfterTaxesAndDiscounts.
     *
     * @param float|null $totalAmountAfterTaxesAndDiscounts
     *
     * @return void
     */
    public function setTotalAmountAfterTaxesAndDiscounts(?float $totalAmountAfterTaxesAndDiscounts): void;

    /**
     * Getter for TotalLogisticsCosts.
     *
     * @return float|null
     */
    public function getTotalLogisticsCosts(): ?float;

    /**
     * Setter for TotalLogisticsCosts.
     *
     * @param float|null $totalLogisticsCosts
     *
     * @return void
     */
    public function setTotalLogisticsCosts(?float $totalLogisticsCosts): void;

    /**
     * Getter for Discounts.
     *
     * @return array|null
     */
    public function getDiscounts(): ?array;

    /**
     * Setter for Discounts.
     *
     * @param array $discounts
     *
     * @return void
     */
    public function setDiscounts(array $discounts): void;

    /**
     * Getter for Taxes.
     *
     * @return array
     */
    public function getTaxes(): ?array;

    /**
     * Setter for Taxes.
     *
     * @param array $taxes
     *
     * @return void
     */
    public function setTaxes(array $taxes): void;
}
