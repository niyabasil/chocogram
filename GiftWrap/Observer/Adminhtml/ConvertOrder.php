<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer\Adminhtml;

use Amasty\GiftWrap\Api\SaleData\WrapRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;

class ConvertOrder implements ObserverInterface
{
    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    public function __construct(WrapRepositoryInterface $wrapRepository)
    {
        $this->wrapRepository = $wrapRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        /** @var Quote $quote */
        $quote = $observer->getQuote();
//        TODO: use this observer for clone wraps from order for new order
//              when order duplicated(edit, reorder actions, etc. )
//        if ($order && $quote) {
//            $oldOrderWraps = $order->getWrapItems();
//            $newQuoteWraps = $quote->getWrapItems();
//            $quoteWrapsMap = [];
//            foreach ($oldOrderWraps as $oldOrderWrap) {
//                $newWrapData = clone $oldOrderWrap;
//                $newWrapData->unsAmGiftWrapEntityId();
//                $newWrapData->unsAmGiftWrapOrderId();
//                $newWrapData->unsAmGiftWrapCardPriceInvoiced();
//                $newWrapData->unsAmGiftWrapBaseCardPriceInvoiced();
//                $newWrapData->unsAmGiftWrapPriceInvoiced();
//                $newWrapData->unsAmGiftWrapBasePriceInvoiced();
//                $newWrapData->unsAmGiftWrapTaxAmountInvoiced();
//                $newWrapData->unsAmGiftWrapBaseTaxAmountInvoiced();
//                $newWrapData->unsAmGiftWrapPriceRefunded();
//                $newWrapData->unsAmGiftWrapBasePriceRefunded();
//                $newWrapData->unsAmGiftWrapPriceCardRefunded();
//                $newWrapData->unsAmGiftWrapBaseCardPriceRefunded();
//                $newWrapData->unsAmGiftWrapTaxAmountRefunded();
//                $newWrapData->unsAmGiftWrapBaseTaxAmountRefunded();
//                $newWrap = $this->wrapRepository->getNewItem();
//                $newWrap->setData($newWrapData->getData());
//                $this->wrapRepository->save($newWrap);
//                $newQuoteWraps[] = $newWrap;
//                $quoteWrapsMap[$oldOrderWrap->getAmGiftWrapEntityId()] = $newWrap->getId();
//            }
//            /** @var OrderItem $orderItem */
//            foreach ($order->getAllItems() as $orderItem) {
//                /** @var QuoteItem $quoteItem */
//                $quoteItem = $quote->getItemsCollection()->getItemByColumnValue(
//                    'convert_from_order_item',
//                    $orderItem->getId()
//                );
//                if ($quoteItem) {
//                    $newWrapItems = [];
//                    foreach ($orderItem->getWrapItems() as $wrapItem) {
//                        $newWrapItem = clone $wrapItem;
//                        $newWrapItem->unsAmGiftWrapEntityId();
//                        $newWrapItem->unsAmGiftWrapOrderItemId();
//                        $newWrapItem->setAmGiftWrapQuoteWrapId($quoteWrapsMap[$wrapItem->getAmGiftWrapQuoteWrapId()]);
//                        $newWrapItems[] = $newWrapItem;
//                    }
//                    $quoteItem->setWrapItems($newWrapItems);
//                }
//            }
//            $quote->setWrapItems($newQuoteWraps);
//        }
    }
}
