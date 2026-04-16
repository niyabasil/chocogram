<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Invoice;

use Amasty\GiftWrap\Model\Total;

class GiftWrap extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\GiftWrapping\Model\Total\Invoice\Giftwrapping
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $wrapIds = [];
        /**
         * Wrapping for items
         */
        $invoiced = 0;
        $baseInvoiced = 0;
        $quoteWraps = $invoice->getOrder()->getWrapItems();
        foreach ($invoice->getAllItems() as $invoiceItem) {
            if (!$invoiceItem->getQty() || $invoiceItem->getQty() == 0) {
                continue;
            }
            $orderItem = $invoiceItem->getOrderItem();
            if (!$orderItem->getWrapItems()) {
                continue;
            }

            foreach ($orderItem->getWrapItems() as $wrapItem) {
                $quoteWrap = $quoteWraps[$wrapItem->getAmGiftWrapQuoteWrapId()] ?? null;
                if (!$quoteWrap) {
                    continue;
                }
                if ($quoteWrap->getAmGiftWrapWrapId() &&
                    $quoteWrap->getAmGiftWrapBasePrice() &&
                    $quoteWrap->getAmGiftWrapBasePrice() != $quoteWrap->getAmGiftWrapBasePriceInvoiced()
                ) {
                    $quoteWrap->setAmGiftWrapBasePriceInvoiced($quoteWrap->getAmGiftWrapBasePrice());
                    $quoteWrap->setAmGiftWrapPriceInvoiced($quoteWrap->getAmGiftWrapPrice());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBasePrice();
                        $invoiced += $quoteWrap->getAmGiftWrapPrice();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBasePrice()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $invoiced += $quoteWrap->getAmGiftWrapPrice()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }

                if ($quoteWrap->getAmGiftWrapCardId() &&
                    $quoteWrap->getAmGiftWrapBaseCardPrice() &&
                    $quoteWrap->getAmGiftWrapBaseCardPrice() != $quoteWrap->getAmGiftWrapBaseCardPriceInvoiced()
                ) {
                    $quoteWrap->setAmGiftWrapBaseCardPriceInvoiced($quoteWrap->getAmGiftWrapBaseCardPrice());
                    $quoteWrap->setAmGiftWrapCardPriceInvoiced($quoteWrap->getAmGiftWrapCardPrice());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBaseCardPrice();
                        $invoiced += $quoteWrap->getAmGiftWrapCardPrice();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBaseCardPrice()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $invoiced += $quoteWrap->getAmGiftWrapCardPrice()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }
                $wrapIds[] = $quoteWrap->getAmGiftWrapEntityId();
            }
        }
        if ($invoiced > 0 || $baseInvoiced > 0) {
            $order->setAmGiftWrapBaseTotalPriceInvoiced($order->getAmGiftWrapBaseTotalPriceInvoiced() + $baseInvoiced);
            $order->setAmGiftWrapTotalPriceInvoiced($order->getAmGiftWrapTotalPriceInvoiced() + $invoiced);
            $invoice->setAmGiftWrapBaseTotalPrice($baseInvoiced);
            $invoice->setAmGiftWrapTotalPrice($invoiced);
        }

        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getAmGiftWrapBaseTotalPrice());
        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getAmGiftWrapTotalPrice());
        return $this;
    }
}
