<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Quote\Tax;

use Amasty\GiftWrap\Api\MessageCardRepositoryInterface;
use Amasty\GiftWrap\Api\WrapRepositoryInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Amasty\GiftWrap\Model\Total;

/**
 * GiftWrapping tax total calculator for quote
 */
class BeforeTax extends AbstractTotal
{
    public const WRAP_TYPE = 'item_gift_wrap';
    public const QUOTE_TYPE = 'quote_gift_wrap';
    public const CARD_TYPE = 'message_card';
    public const CODE_WRAP = 'item_gift_wrap';
    public const CODE_QUOTE = 'quote_gift_wrap';
    public const CODE_CARD = 'message_card';

    /**
     * @var \Magento\Quote\Model\Quote|\Magento\Quote\Model\Quote\Address
     */
    private $quoteEntity;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Amasty\GiftWrap\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var MessageCardRepositoryInterface
     */
    private $cardRepository;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var bool
     */
    private $oneWrapPerAddress = false;

    public function __construct(
        \Amasty\GiftWrap\Model\ConfigProvider $configProvider,
        PriceCurrencyInterface $priceCurrency,
        WrapRepositoryInterface $wrapRepository,
        MessageCardRepositoryInterface $cardRepository
    ) {
        $this->configProvider = $configProvider;
        $this->priceCurrency = $priceCurrency;
        $this->wrapRepository = $wrapRepository;
        $this->cardRepository = $cardRepository;
        $this->setCode('am_gift_wrap_tax_before');
    }

    /**
     * Collect gift wrapping related items and add them to tax calculation
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() != Address::TYPE_SHIPPING) {
            return $this;
        }

        $this->store = $quote->getStore();
        if ($quote->getIsMultiShipping()
            && $shippingAssignment->getShipping()->getAddress()->getItemsCollection()->getSize()
        ) {
            $this->quoteEntity = $shippingAssignment->getShipping()->getAddress();
            $this->oneWrapPerAddress = true;
        } else {
            $this->quoteEntity = $quote;
        }

        if ($this->oneWrapPerAddress) {
            $this->collectItemsForQuote($shippingAssignment, $this->configProvider->getWrapTaxClassId());
            $this->collectCardsForQuote($shippingAssignment, $this->configProvider->getCardTaxClassId());
        } else {
            $this->collectItems($shippingAssignment, $total, $this->configProvider->getWrapTaxClassId());
            $this->collectCards($shippingAssignment, $total, $this->configProvider->getCardTaxClassId());
        }

        return $this;
    }

    /**
     * Collect wrapping tax total for items
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @param   int $gwTaxClassId
     * @return  $this
     */
    private function collectItems($shippingAssignment, $total, $taxClassId)
    {
        $wrapCodeMapping = [];
        $wrapIds = [];
        $quoteWraps = $this->quoteEntity->getWrapItems();
        foreach ($shippingAssignment->getItems() as $item) {
            $wrapItems = $item->getWrapItems();
            if (!$wrapItems || $item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }

            $associatedTaxables = [];
            $existTaxables = $item->getAssociatedTaxables();
            foreach ($wrapItems as $wrapItem) {
                $quoteWrap = $quoteWraps[$wrapItem->getAmGiftWrapQuoteWrapId()] ?? null;
                if (!$quoteWrap
                    || $quoteWrap->getIsDeleted()
                    || (Total::WRAP_TOGETHER && in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds))
                ) {
                    continue;
                }

                $wrapIds[] = $quoteWrap->getAmGiftWrapEntityId();
                $uniqueId = 'wrap' . $wrapItem->getAmGiftWrapQuoteWrapId();
                if ($this->checkExistTax($uniqueId, $existTaxables)) {
                    continue;
                }

                if (!($wrapBasePrice = $quoteWrap->getAmGiftWrapBasePrice())) {
                    $wrapBasePrice = $this->wrapRepository->getById(
                        $quoteWrap->getAmGiftWrapWrapId(),
                        $this->store->getId()
                    )->getPrice();
                }

                $wrapPrice = $this->priceCurrency->convert($wrapBasePrice, $this->store);
                $wrapCode = self::CODE_WRAP . $this->getNextIncrement();

                $wrapCodeMapping[$wrapCode] = $quoteWrap;

                $associatedTaxables[] = [
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => self::WRAP_TYPE,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => $wrapCode,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $wrapPrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $wrapBasePrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $taxClassId,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => false,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE
                    => CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE,
                    'unique_id' => $uniqueId
                ];
            }

            if ($existTaxables) {
                // Pipeline failed for array_merge in loop
                // @codingStandardsIgnoreLine
                $associatedTaxables = array_merge($existTaxables, $associatedTaxables);
            }

            $item->setAssociatedTaxables($associatedTaxables);
        }

        $total->setAmastyGiftWrapCodeMapping($wrapCodeMapping);

        return $this;
    }

    /**
     * Collect printed card tax total for quote
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param int $gwTaxClassId
     * @return $this
     */
    private function collectCards($shippingAssignment, $total, $taxClassId)
    {
        $cardCodeMapping = [];
        $wrapIds = [];
        $quoteWraps = $this->quoteEntity->getWrapItems();
        foreach ($shippingAssignment->getItems() as $item) {
            $wrapItems = $item->getWrapItems();
            if ($item->getProduct()->isVirtual() || $item->getParentItem() || !$wrapItems) {
                continue;
            }
            $associatedTaxables = [];
            $existTaxables = $item->getAssociatedTaxables();
            foreach ($wrapItems as $wrapItem) {
                $quoteWrap = $quoteWraps[$wrapItem->getAmGiftWrapQuoteWrapId()] ?? null;
                if (!$quoteWrap
                    || $quoteWrap->getIsDeleted()
                    || (Total::WRAP_TOGETHER && in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)
                    || !$quoteWrap->getAmGiftWrapCardId())
                ) {
                    continue;
                }

                $wrapIds[] = $quoteWrap->getAmGiftWrapEntityId();
                $uniqueId = 'card' . $wrapItem->getAmGiftWrapQuoteWrapId();
                if ($this->checkExistTax($uniqueId, $existTaxables)) {
                    continue;
                }

                if (!$cardBasePrice = $quoteWrap->getAmGiftWrapBaseCardPrice()) {
                    $cardBasePrice = $this->cardRepository->getById(
                        $quoteWrap->getAmGiftWrapCardId(),
                        $this->store->getId()
                    )->getPrice();
                }

                $cardPrice = $this->priceCurrency->convert($cardBasePrice, $this->store);
                $cardCode = self::CODE_CARD . $this->getNextIncrement();

                $cardCodeMapping[$cardCode] = $quoteWrap;

                $qty = Total::WRAP_TOGETHER ? 1 : $quoteWrap->getAmGiftWrapWrapQty();
                $associatedTaxables[] = [
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => self::CARD_TYPE,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => $cardCode,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $cardPrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $cardBasePrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => $qty,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $taxClassId,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => false,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE
                    => CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE,
                    'unique_id' => $uniqueId
                ];
            }
            if ($existTaxables) {
                // Pipeline failed for array_merge in loop
                // @codingStandardsIgnoreLine
                $associatedTaxables = array_merge($existTaxables, $associatedTaxables);
            }
            $item->setAssociatedTaxables($associatedTaxables);
        }

        $total->setAmastyGiftWrapCardCodeMapping($cardCodeMapping);

        return $this;
    }

    /**
     * @param $shippingAssignment
     * @param $taxClassId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectItemsForQuote($shippingAssignment, $taxClassId)
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        $wrapItems = $address->getWrapItems();
        if ($wrapItems) {
            $associatedTaxables = $address->getAssociatedTaxables();
            if (!$associatedTaxables) {
                $associatedTaxables = [];
            }

            $quoteWrap = array_shift($wrapItems);
            if ($quoteWrap
                && !$quoteWrap->getIsDeleted()
            ) {
                if (!$wrapBasePrice = $quoteWrap->getAmGiftWrapBasePrice()) {
                    $wrapBasePrice = $this->wrapRepository->getById(
                        $quoteWrap->getAmGiftWrapWrapId(),
                        $this->store->getId()
                    )->getPrice();
                }
                $wrapPrice = $this->priceCurrency->convert($wrapBasePrice, $this->store);

                $associatedTaxables[] = [
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => self::WRAP_TYPE,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => self::CODE_WRAP,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $wrapPrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $wrapBasePrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $taxClassId,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => false,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE
                    => CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE,
                ];
            }

            $address->setAssociatedTaxables($associatedTaxables);
        }

        return $this;
    }

    /**
     * @param $shippingAssignment
     * @param $taxClassId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectCardsForQuote($shippingAssignment, $taxClassId)
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        $wrapItems = $address->getWrapItems();
        if ($wrapItems) {
            $associatedTaxables = $address->getAssociatedTaxables();
            if (!$associatedTaxables) {
                $associatedTaxables = [];
            }

            $quoteWrap = array_shift($wrapItems);
            if ($quoteWrap
                && !$quoteWrap->getIsDeleted()
                && $quoteWrap->getAmGiftWrapCardId()
            ) {
                if (!$cardBasePrice = $quoteWrap->getAmGiftWrapBaseCardPrice()) {
                    $cardBasePrice = $this->cardRepository->getById(
                        $quoteWrap->getAmGiftWrapCardId(),
                        $this->store->getId()
                    )->getPrice();
                }
                $cardPrice = $this->priceCurrency->convert($cardBasePrice, $this->store);

                $associatedTaxables[] = [
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => self::CARD_TYPE,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => self::CODE_CARD,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $cardPrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $cardBasePrice,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $taxClassId,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => false,
                    CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE
                    => CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE,
                ];
            }

            $address->setAssociatedTaxables($associatedTaxables);
        }

        return $this;
    }

    /**
     * Assign wrapping tax totals and labels to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return null;
    }

    /**
     * Increment and return counter. This function is intended to be used to generate temporary
     * id for an item.
     *
     * @return int
     */
    private function getNextIncrement()
    {
        return ++$this->counter;
    }

    /**
     * fix bug on 223 - twice added our eitems
     * @param $id
     * @param $existTaxables
     *
     * @return bool
     */
    protected function checkExistTax($id, $existTaxables)
    {
        if (is_array($existTaxables)) {
            foreach ($existTaxables as $item) {
                $uniqueId = $item['unique_id'] ?? null;
                if ($uniqueId && $uniqueId === $id) {
                    return true;
                }
            }
        }

        return false;
    }
}
