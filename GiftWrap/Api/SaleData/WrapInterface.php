<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Api\SaleData;

interface WrapInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'entity_id';
    public const WRAP_ID = 'wrap_id';
    public const CARD_ID = 'card_id';
    public const IS_RECEIPT_HIDDEN = 'is_receipt_hidden';
    public const GIFT_MESSAGE = 'gift_message';
    public const PRICE = 'price';
    public const BASE_PRICE = 'base_price';
    public const CARD_PRICE = 'card_price';
    public const BASE_CARD_PRICE = 'base_card_price';
    public const TAX_AMOUNT = 'tax_amount';
    public const BASE_TAX_AMOUNT = 'base_tax_amount';
    public const CARD_TAX_AMOUNT = 'card_tax_amount';
    public const BASE_CARD_TAX_AMOUNT = 'base_card_tax_amount';
    public const PRICE_INCL_TAX = 'price_incl_tax';
    public const BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    public const CARD_PRICE_INCL_TAX = 'card_price_incl_tax';
    public const BASE_CARD_PRICE_INCL_TAX = 'base_card_price_incl_tax';
    public const WRAP_NAME = 'wrap_name';
    public const CARD_NAME = 'card_name';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getWrapId();

    /**
     * @param int|null $wrapId
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setWrapId($wrapId);

    /**
     * @return int|null
     */
    public function getCardId();

    /**
     * @param int|null $cardId
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setCardId($cardId);

    /**
     * @return int|null
     */
    public function getIsReceiptHidden();

    /**
     * @param int|null $isReceiptHidden
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setIsReceiptHidden($isReceiptHidden);

    /**
     * @return string|null
     */
    public function getGiftMessage();

    /**
     * @param string|null $giftMessage
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setGiftMessage($giftMessage);

    /**
     * @return float|null
     */
    public function getPrice();

    /**
     * @param float|null $price
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setPrice($price);

    /**
     * @return float|null
     */
    public function getBasePrice();

    /**
     * @param float|null $basePrice
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setBasePrice($basePrice);

    /**
     * @return float|null
     */
    public function getCardPrice();

    /**
     * @param float|null $cardPrice
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setCardPrice($cardPrice);

    /**
     * @return float|null
     */
    public function getBaseCardPrice();

    /**
     * @param float|null $baseCardPrice
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setBaseCardPrice($baseCardPrice);

    /**
     * @return float|null
     */
    public function getTaxAmount();

    /**
     * @param float|null $taxAmount
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setTaxAmount($taxAmount);

    /**
     * @return float|null
     */
    public function getBaseTaxAmount();

    /**
     * @param float|null $baseTaxAmount
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setBaseTaxAmount($baseTaxAmount);

    /**
     * @return float|null
     */
    public function getCardTaxAmount();

    /**
     * @param float|null $cardTaxAmount
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setCardTaxAmount($cardTaxAmount);

    /**
     * @return float|null
     */
    public function getBaseCardTaxAmount();

    /**
     * @param float|null $baseCardTaxAmount
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setBaseCardTaxAmount($baseCardTaxAmount);

    /**
     * @return float|null
     */
    public function getPriceInclTax();

    /**
     * @param float|null $priceInclTax
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setPriceInclTax($priceInclTax);

    /**
     * @return float|null
     */
    public function getBasePriceInclTax();

    /**
     * @param float|null $basePriceInclTax
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setBasePriceInclTax($basePriceInclTax);

    /**
     * @return float|null
     */
    public function getCardPriceInclTax();

    /**
     * @param float|null $cardPriceInclTax
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setCardPriceInclTax($cardPriceInclTax);

    /**
     * @return float|null
     */
    public function getBaseCardPriceInclTax();

    /**
     * @param float|null $baseCardPriceInclTax
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function setBaseCardPriceInclTax($baseCardPriceInclTax);
}
