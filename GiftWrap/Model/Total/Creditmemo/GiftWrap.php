<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Creditmemo;

use Amasty\GiftWrap\Model\Total;

class GiftWrap extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @param   \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return  \Magento\GiftWrapping\Model\Total\Creditmemo\Giftwrapping
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        /**
         * Wrapping for items
         */
        $refunded = 0;
        $baseRefunded = 0;
        $wrapIds = [];
        $quoteWraps = $creditmemo->getOrder()->getWrapItems();
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            if (!$creditmemoItem->getQty() || $creditmemoItem->getQty() == 0) {
                continue;
            }
            $orderItem = $creditmemoItem->getOrderItem();

            if (!$orderItem->getWrapItems()) {
                continue;
            }

            foreach ($orderItem->getWrapItems() as $wrapItem) {
                $quoteWrap = $quoteWraps[$wrapItem->getAmGiftWrapQuoteWrapId()] ?? null;
                if (!$quoteWrap) {
                    continue;
                }
                if ($quoteWrap->getAmGiftWrapWrapId() &&
                    $quoteWrap->getAmGiftWrapBasePriceInvoiced() &&
                    $quoteWrap->getAmGiftWrapBasePriceInvoiced() != $quoteWrap->getAmGiftWrapBasePriceRefunded()
                ) {
                    $quoteWrap->setAmGiftWrapBasePriceRefunded($quoteWrap->getAmGiftWrapBasePriceInvoiced());
                    $quoteWrap->setAmGiftWrapPriceRefunded($quoteWrap->getAmGiftWrapPriceInvoiced());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBasePriceInvoiced();
                        $refunded += $quoteWrap->getAmGiftWrapPriceInvoiced();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBasePriceInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $refunded += $quoteWrap->getAmGiftWrapPriceInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }

                if ($quoteWrap->getAmGiftWrapCardId() &&
                    $quoteWrap->getAmGiftWrapBaseCardPriceInvoiced() &&
                    $quoteWrap->getAmGiftWrapBaseCardPriceInvoiced() != $quoteWrap->getAmGiftWrapBaseCardPriceRefunded()
                ) {
                    $quoteWrap->setAmGiftWrapBaseCardPriceRefunded($quoteWrap->getAmGiftWrapBaseCardPriceInvoiced());
                    $quoteWrap->setAmGiftWrapPriceCardRefunded($quoteWrap->getAmGiftWrapCardPriceInvoiced());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBaseCardPriceInvoiced();
                        $refunded += $quoteWrap->getAmGiftWrapCardPriceInvoiced();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBaseCardPriceInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $refunded += $quoteWrap->getAmGiftWrapCardPriceInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }
                $wrapIds[] = $quoteWrap->getAmGiftWrapEntityId();
            }
        }
        if ($refunded > 0 || $baseRefunded > 0) {
            $order->setAmGiftWrapBaseTotalPriceRefunded($order->getAmGiftWrapBaseTotalPriceRefunded() + $baseRefunded);
            $order->setAmGiftWrapTotalPriceRefunded($order->getAmGiftWrapTotalPriceRefunded() + $refunded);
            $creditmemo->setAmGiftWrapBaseTotalPrice($baseRefunded);
            $creditmemo->setAmGiftWrapTotalPrice($refunded);
        }
        
        $creditmemo->setBaseGrandTotal(
            $creditmemo->getBaseGrandTotal() +
            $creditmemo->getAmGiftWrapBaseTotalPrice()
        );
        $creditmemo->setGrandTotal(
            $creditmemo->getGrandTotal() +
            $creditmemo->getAmGiftWrapTotalPrice()
        );

        $creditmemo->setBaseCustomerBalanceReturnMax(
            $creditmemo->getBaseCustomerBalanceReturnMax() +
            $creditmemo->getAmGiftWrapBaseTotalPrice()
        );
        $creditmemo->setCustomerBalanceReturnMax(
            $creditmemo->getCustomerBalanceReturnMax() +
            $creditmemo->getAmGiftWrapTotalPrice()
        );

        return $this;
    }
}
