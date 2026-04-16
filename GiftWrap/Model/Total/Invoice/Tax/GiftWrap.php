<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Invoice\Tax;

use Amasty\GiftWrap\Model\Total;

class GiftWrap extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\GiftWrapping\Model\Total\Invoice\Tax\Giftwrapping
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $wrapIds = [];

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
                if ($quoteWrap->getAmGiftWrapEntityId()
                    && $quoteWrap->getAmGiftWrapBaseTaxAmount()
                    && $quoteWrap->getAmGiftWrapBaseTaxAmount() != $quoteWrap->getAmGiftWrapBaseTaxAmountInvoiced()
                ) {
                    $quoteWrap->setAmGiftWrapBaseTaxAmountInvoiced($quoteWrap->getAmGiftWrapBaseTaxAmount());
                    $quoteWrap->setAmGiftWrapTaxAmountInvoiced($quoteWrap->getAmGiftWrapTaxAmount());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBaseTaxAmount();
                        $invoiced += $quoteWrap->getAmGiftWrapTaxAmount();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBaseTaxAmount()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $invoiced += $quoteWrap->getAmGiftWrapTaxAmount()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }

                if ($quoteWrap->getAmGiftWrapCardId()
                    && $quoteWrap->getAmGiftWrapBaseCardTaxAmount()
                    && $quoteWrap->getAmGiftWrapBaseCardTaxAmount()
                        != $quoteWrap->getAmGiftWrapBaseCardTaxAmountInvoiced()
                ) {
                    $quoteWrap->setAmGiftWrapBaseCardTaxAmountInvoiced($quoteWrap->getAmGiftWrapBaseCardTaxAmount());
                    $quoteWrap->setAmGiftWrapCardTaxAmountInvoiced($quoteWrap->getAmGiftWrapCardTaxAmount());
                    if (Total::WRAP_TOGETHER && !in_array($quoteWrap->getAmGiftWrapEntityId(), $wrapIds)) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBaseCardTaxAmount();
                        $invoiced += $quoteWrap->getAmGiftWrapCardTaxAmount();
                    } elseif (!Total::WRAP_TOGETHER) {
                        $baseInvoiced += $quoteWrap->getAmGiftWrapBaseCardTaxAmount()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                        $invoiced += $quoteWrap->getAmGiftWrapCardTaxAmount()
                            * min($invoiceItem->getQty(), $quoteWrap->getAmGiftWrapWrapQty());
                    }
                }

                $wrapIds[] = $quoteWrap->getAmGiftWrapEntityId();
            }
        }

        // used when one wrap per order (ex. multishipping)
        if ($order->getWrapItems()
            && $baseInvoiced == 0
            && $order->getAmGiftWrapBaseTotalTaxAmount()
            && $order->getAmGiftWrapBaseTotalTaxAmount() != $order->getAmGiftWrapBaseTotalTaxAmountInvoiced()
        ) {
            $invoiced += $order->getAmGiftWrapTotalTaxAmount();
            $baseInvoiced += $order->getAmGiftWrapBaseTotalTaxAmount();
        }

        if ($invoiced > 0 || $baseInvoiced > 0) {
            $order->setAmGiftWrapBaseTotalTaxAmountInvoiced(
                $order->getAmGiftWrapBaseTotalTaxAmountInvoiced() + $baseInvoiced
            );
            $order->setAmGiftWrapTotalTaxAmountInvoiced($order->getAmGiftWrapTotalTaxAmountInvoiced() + $invoiced);
            $invoice->setAmGiftWrapBaseTotalTaxAmount($baseInvoiced);
            $invoice->setAmGiftWrapTotalTaxAmount($invoiced);
        }

        if (!$invoice->isLast()) {
            $baseTaxAmount = $invoice->getAmGiftWrapBaseTotalTaxAmount() +
                $invoice->getAmGiftWrapBaseTaxAmount() +
                $invoice->getAmGiftWrapCardBaseTaxAmount();
            $taxAmount = $invoice->getAmGiftWrapTotalTaxAmount();
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseTaxAmount);
            $invoice->setTaxAmount($invoice->getTaxAmount() + $taxAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTaxAmount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $taxAmount);
        }

        return $this;
    }
}
