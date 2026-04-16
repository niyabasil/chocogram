<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\SaleData\Quote\Wrap;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Item\AbstractItem;

abstract class AbstractWrap extends AbstractModel
{
    /**
     * @inheritdoc
     */
    public function setData($key, $value = null)
    {
        if (is_string($key) && strpos($key, 'am_gift_wrap_') === false) {
            $this->setData('am_gift_wrap_' . $key, $value);
        }
        return parent::setData($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_getData(WrapInterface::ID);
    }

    /**
     * @inheritdoc
     */
    public function getWrapId()
    {
        return $this->_getData(WrapInterface::WRAP_ID);
    }

    /**
     * @inheritdoc
     */
    public function setWrapId($wrapId)
    {
        $this->setData(WrapInterface::WRAP_ID, $wrapId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCardId()
    {
        return $this->_getData(WrapInterface::CARD_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCardId($cardId)
    {
        $this->setData(WrapInterface::CARD_ID, $cardId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsReceiptHidden()
    {
        return $this->_getData(WrapInterface::IS_RECEIPT_HIDDEN);
    }

    /**
     * @inheritdoc
     */
    public function setIsReceiptHidden($isReceiptHidden)
    {
        $this->setData(WrapInterface::IS_RECEIPT_HIDDEN, $isReceiptHidden);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGiftMessage()
    {
        return $this->_getData(WrapInterface::GIFT_MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setGiftMessage($giftMessage)
    {
        $this->setData(WrapInterface::GIFT_MESSAGE, $giftMessage);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->_getData(WrapInterface::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        $this->setData(WrapInterface::PRICE, $price);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBasePrice()
    {
        return $this->_getData(WrapInterface::BASE_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setBasePrice($basePrice)
    {
        $this->setData(WrapInterface::BASE_PRICE, $basePrice);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCardPrice()
    {
        return $this->_getData(WrapInterface::CARD_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setCardPrice($cardPrice)
    {
        $this->setData(WrapInterface::CARD_PRICE, $cardPrice);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseCardPrice()
    {
        return $this->_getData(WrapInterface::BASE_CARD_PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setBaseCardPrice($baseCardPrice)
    {
        $this->setData(WrapInterface::BASE_CARD_PRICE, $baseCardPrice);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxAmount()
    {
        return $this->_getData(WrapInterface::TAX_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setTaxAmount($taxAmount)
    {
        $this->setData(WrapInterface::TAX_AMOUNT, $taxAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseTaxAmount()
    {
        return $this->_getData(WrapInterface::BASE_TAX_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setBaseTaxAmount($baseTaxAmount)
    {
        $this->setData(WrapInterface::BASE_TAX_AMOUNT, $baseTaxAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCardTaxAmount()
    {
        return $this->_getData(WrapInterface::CARD_TAX_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setCardTaxAmount($cardTaxAmount)
    {
        $this->setData(WrapInterface::CARD_TAX_AMOUNT, $cardTaxAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseCardTaxAmount()
    {
        return $this->_getData(WrapInterface::BASE_CARD_TAX_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setBaseCardTaxAmount($baseCardTaxAmount)
    {
        $this->setData(WrapInterface::BASE_CARD_TAX_AMOUNT, $baseCardTaxAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriceInclTax()
    {
        return $this->_getData(WrapInterface::PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function setPriceInclTax($priceInclTax)
    {
        $this->setData(WrapInterface::PRICE_INCL_TAX, $priceInclTax);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBasePriceInclTax()
    {
        return $this->_getData(WrapInterface::BASE_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function setBasePriceInclTax($basePriceInclTax)
    {
        $this->setData(WrapInterface::BASE_PRICE_INCL_TAX, $basePriceInclTax);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCardPriceInclTax()
    {
        return $this->_getData(WrapInterface::CARD_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function setCardPriceInclTax($cardPriceInclTax)
    {
        $this->setData(WrapInterface::CARD_PRICE_INCL_TAX, $cardPriceInclTax);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseCardPriceInclTax()
    {
        return $this->_getData(WrapInterface::BASE_CARD_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function setBaseCardPriceInclTax($baseCardPriceInclTax)
    {
        $this->setData(WrapInterface::BASE_CARD_PRICE_INCL_TAX, $baseCardPriceInclTax);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWrapName()
    {
        return $this->_getData(WrapInterface::BASE_CARD_PRICE_INCL_TAX);
    }

    /**
     * @inheritdoc
     */
    public function setWrapName($wrapName)
    {
        $this->setData(WrapInterface::WRAP_NAME, $wrapName);

        return $this;
    }

    /**
     * Fill wrapped qty. If $qty = null , try to fill all Quote Item qty
     *
     * @param AbstractItem $item
     * @param null|int $qty
     * @return Wrap
     */
    public function addForItem(AbstractItem $item, $qty = null)
    {
        $wrapItems = $item->getWrapItems() ?? [];
        if ($qty === null) {
            $qty = $item->getQty();
        }

        foreach ($wrapItems as $wrapItem) {
            if ($wrapItem->getAmGiftWrapQuoteWrapId() == $this->getId()) {
                $wrapItem->setAmGiftWrapWrapQty($wrapItem->getAmGiftWrapWrapQty() + $qty);
                $wrapItem->setIsModified(true);
                return $this;
            }
        }

        $wrapItems[] = new DataObject([
            'am_gift_wrap_quote_wrap_id' => $this->getId(),
            'am_gift_wrap_quote_item_id' => $item->getId(),
            'am_gift_wrap_wrap_qty' => $qty,
            'is_modified' => true
        ]);

        $item->setWrapItems($wrapItems);

        return $this;
    }

    /**
     * @param AbstractItem $item
     * @return AbstractItem
     */
    public function deleteFromItem(AbstractItem $item)
    {
        $wrapItems = $item->getWrapItems();
        foreach ($wrapItems as $wrapItem) {
            if ($wrapItem->getWrapId() == $this->getEntityId()) {
                $wrapItem->setIsDeleted(true);
            }
        }

        return $item;
    }
}
