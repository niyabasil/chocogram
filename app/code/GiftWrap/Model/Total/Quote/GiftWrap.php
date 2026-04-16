<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Quote;

use Amasty\GiftWrap\Api\MessageCardRepositoryInterface;
use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Api\SaleData\WrapRepositoryInterface as QuoteWrapRepository;
use Amasty\GiftWrap\Api\WrapRepositoryInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Amasty\GiftWrap\Model\Total;

class GiftWrap extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Magento\Quote\Model\Quote|\Magento\Quote\Model\Quote\Address
     */
    private $quoteEntity;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var MessageCardRepositoryInterface
     */
    private $cardRepository;

    /**
     * @var QuoteWrapRepository
     */
    private $quoteWrapRepository;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        WrapRepositoryInterface $wrapRepository,
        MessageCardRepositoryInterface $cardRepository,
        QuoteWrapRepository $quoteWrapRepository
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->wrapRepository = $wrapRepository;
        $this->cardRepository = $cardRepository;
        $this->setCode('am_gift_wrap_quote');
        $this->quoteWrapRepository = $quoteWrapRepository;
    }

    /**
     * Collect gift wrapping totals
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
        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() !== Address::TYPE_SHIPPING) {
            return $this;
        }

        $this->store = $quote->getStore();
        if ($quote->getIsMultiShipping()
            && $shippingAssignment->getShipping()->getAddress()->getItemsCollection()->getSize()
        ) {
            $this->quoteEntity = $shippingAssignment->getShipping()->getAddress();
        } else {
            $this->quoteEntity = $quote;
        }

        $total = $this->collectItems($shippingAssignment, $total);

        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $total->getAmGiftWrapBaseTotalPrice());
        $total->setGrandTotal($total->getGrandTotal() + $total->getAmGiftWrapTotalPrice());

        $quote->setAmGiftWrapBaseTotalPrice(0);
        $quote->setAmGiftWrapTotalPrice(0);

        $quote->setAmGiftWrapBaseTotalPrice(
            $total->getAmGiftWrapBaseTotalPrice() + $quote->getAmGiftWrapBaseTotalPrice()
        );
        $quote->setAmGiftWrapTotalPrice($total->getAmGiftWrapTotalPrice() + $quote->getAmGiftWrapTotalPrice());

        return $this;
    }

    /**
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return \Magento\Quote\Model\Quote\Address\Total $total
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function collectItems($shippingAssignment, $total)
    {
        $items = $shippingAssignment->getItems();
        $baseTotalAmount = false;
        $totalAmount = false;
        $wrapIds = [];

        $quoteWraps = $this->quoteEntity->getWrapItems();
        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem() || !$item->getWrapItems()) {
                continue;
            }
            foreach ($item->getWrapItems() as $wrapItem) {
                $quoteWrap = $quoteWraps[$wrapItem->getAmGiftWrapQuoteWrapId()] ?? null;
                if (!$quoteWrap || $quoteWrap->getIsDeleted() || $wrapItem->getIsDeleted()) {
                    continue;
                }

                if (!$wrapBasePrice = $quoteWrap->getAmGiftWrapBasePrice()) {
                    $wrapBasePrice = $this->wrapRepository->getById(
                        $quoteWrap->getAmGiftWrapWrapId(),
                        $this->store->getId()
                    )->getPrice();
                }
                $wrapPrice = $this->priceCurrency->convert($wrapBasePrice, $this->store);
                $quoteWrap->setAmGiftWrapBasePrice($wrapBasePrice);
                $quoteWrap->setAmGiftWrapPrice($wrapPrice);

                $cardBasePrice = 0;
                $cardPrice = 0;
                if ($quoteWrap->getAmGiftWrapCardId()) {
                    if (!$cardBasePrice = $quoteWrap->getAmGiftWrapBaseCardPrice()) {
                        $cardBasePrice = $this->cardRepository->getById(
                            $quoteWrap->getAmGiftWrapCardId(),
                            $this->store->getId()
                        )->getPrice();
                    }

                    $cardPrice = $this->priceCurrency->convert($cardBasePrice, $this->store);
                    $quoteWrap->setAmGiftWrapBaseCardPrice($cardBasePrice);
                    $quoteWrap->setAmGiftWrapCardPrice($cardPrice);
                }

                if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                    $baseTotalAmount += ($wrapBasePrice + $cardBasePrice);
                    $totalAmount += ($wrapPrice + $cardPrice);
                } elseif (!Total::WRAP_TOGETHER) {
                    $baseTotalAmount += ($wrapBasePrice + $cardBasePrice) * $quoteWrap->getAmGiftWrapWrapQty();
                    $totalAmount += ($wrapPrice + $cardPrice) * $quoteWrap->getAmGiftWrapWrapQty();
                }

                $wrapIds[] = $quoteWrap->getAmGiftWrapEntityId();
            }
        }
        $total->setAmGiftWrapWrapIdsCount(count($wrapIds));
        $total->setAmGiftWrapBaseTotalPrice($baseTotalAmount);
        $total->setAmGiftWrapTotalPrice($totalAmount);

        return $total;
    }

    /**
     * Assign wrapping totals and labels to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => $this->getCode(),
            'title' => __('Gift Wrapping'),
            'am_gift_wrap_total_price' => $total->getAmGiftWrapTotalPrice(),
            'am_gift_wrap_base_total_price' => $total->getAmGiftWrapBaseTotalPrice(),
            'am_gift_wrap_wrap_ids_count' => $total->getAmGiftWrapWrapIdsCount(),
            'am_gift_wrap_total_tax_amount' => $total->getAmGiftWrapTotalTaxAmount(),
            'am_gift_wrap_base_total_tax_amount' => $total->getAmGiftWrapBaseTotalTaxAmount(),
            'am_gift_wrap_total_price_incl_tax' => $total->getAmGiftWrapTotalPriceInclTax(),
            'am_gift_wrap_base_total_price_incl_tax' => $total->getAmGiftWrapBaseTotalPriceInclTax()
        ];
    }
}
