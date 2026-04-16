<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

class ConvertOrderItem implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var OrderItem $orderItem */
        $orderItem = $observer->getOrderItem();
        /** @var QuoteItem $quoteItem */
        $quoteItem = $observer->getQuoteItem();
        $quoteItem->setData('convert_from_order_item', $orderItem->getId());
    }
}
