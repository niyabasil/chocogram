<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Creditmemo\Tax;

use Amasty\GiftWrap\Model\Total;

class GiftWrap extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return \Magento\GiftWrapping\Model\Total\Creditmemo\Tax\Giftwrapping
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
        $quoteWraps = $order->getWrapItems();
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
                if ($quoteWrap->getAmGiftWrapEntityId()
                    && $quoteWrap->getAmGiftWrapBaseTaxAmountInvoiced()
                    && $quoteWrap->getAmGiftWrapBaseTaxAmountInvoiced()
                        != $quoteWrap->getAmGiftWrapBaseTaxAmountRefunded()
                ) {
                    $quoteWrap->setAmGiftWrapBaseTaxAmountRefunded($quoteWrap->getAmGiftWrapBaseTaxAmountInvoiced());
                    $quoteWrap->setAmGiftWrapTaxAmountRefunded($quoteWrap->getAmGiftWrapTaxAmountInvoiced());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBaseTaxAmountInvoiced();
                        $refunded += $quoteWrap->getAmGiftWrapTaxAmountInvoiced();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBaseTaxAmountInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $refunded += $quoteWrap->getAmGiftWrapTaxAmountInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }

                if ($quoteWrap->getAmGiftWrapCardId()
                    && $quoteWrap->getAmGiftWrapBaseCardTaxAmountInvoiced()
                    && $quoteWrap->getAmGiftWrapBaseCardTaxAmountInvoiced()
                        != $quoteWrap->getAmGiftWrapBaseCardTaxAmountRefunded()
                ) {
                    $quoteWrap->setAmGiftWrapBaseCardTaxAmountRefunded(
                        $quoteWrap->getAmGiftWrapBaseCardTaxAmountInvoiced()
                    );
                    $quoteWrap->setAmGiftWrapCardTaxAmountRefunded($quoteWrap->getAmGiftWrapCardTaxAmountInvoiced());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBaseCardTaxAmountInvoiced();
                        $refunded += $quoteWrap->getAmGiftWrapCardTaxAmountInvoiced();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseRefunded += $quoteWrap->getAmGiftWrapBaseCardTaxAmountInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $refunded += $quoteWrap->getAmGiftWrapCardTaxAmountInvoiced()
                            * min($creditmemoItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }
                $wrapIds[] = $quoteWrap->getAmGiftWrapEntityId();
            }
        }

        // used when one wrap per order (ex. multishipping)
        if ($order->getWrapItems()
            && $baseRefunded == 0
            && $order->getAmGiftWrapBaseTotalTaxAmountInvoiced()
            && $order->getAmGiftWrapBaseTotalTaxAmountInvoiced() != $order->getAmGiftWrapBaseTotalTaxAmountRefunded()
        ) {
            $refunded += $order->getAmGiftWrapTotalTaxAmount();
            $baseRefunded += $order->getAmGiftWrapBaseTotalTaxAmount();
        }

        if ($refunded > 0 || $baseRefunded > 0) {
            $order->setAmGiftWrapBaseTotalTaxAmountRefunded(
                $order->getAmGiftWrapBaseTotalTaxAmountRefunded() + $baseRefunded
            );
            $order->setAmGiftWrapTotalTaxAmountRefunded($order->getAmGiftWrapTotalTaxAmountRefunded() + $refunded);
            $creditmemo->setAmGiftWrapBaseTotalTaxAmount($baseRefunded);
            $creditmemo->setAmGiftWrapTotalTaxAmount($refunded);
        }

        return $this;
    }
}
