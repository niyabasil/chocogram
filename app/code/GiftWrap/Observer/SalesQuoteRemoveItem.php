<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * event = sales_quote_remove_item
 */
class SalesQuoteRemoveItem implements ObserverInterface
{
    /**
     * @var \Amasty\GiftWrap\Model\SaleData\Quote\WrapRepository
     */
    private $wrapRepository;

    public function __construct(\Amasty\GiftWrap\Model\SaleData\Quote\WrapRepository $wrapRepository)
    {
        $this->wrapRepository = $wrapRepository;
    }

    /**
     * Delete a wrap if a wrapped item is the only item in the wrap.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getData('quote_item');

        $wrapItems = $item->getData('wrap_items');

        if (!$wrapItems) {
            return;
        }

        $quote = $item->getQuote();
        $quote->setData('is_wrap_save_disabled', true);
        $quoteItems = $quote->getItems();

        foreach ($wrapItems as $wrapItem) {
            $wrapId = $wrapItem->getData('am_gift_wrap_quote_wrap_id');
            $wrapItem->setIsDeleted(true);

            foreach ($quoteItems as $quoteItem) {
                if ($quoteItem->getItemId() === $item->getItemId()) {
                    continue;
                }
                $quoteWrapItems = $quoteItem->getData('wrap_items');
                if (!$quoteWrapItems) {
                    continue;
                }
                foreach ($quoteWrapItems as $quoteWrapItem) {
                    if ($quoteWrapItem->getData('am_gift_wrap_quote_wrap_id') === $wrapId) {
                        continue 3;
                    }
                }
            }

            $this->wrapRepository->deleteById($wrapId);
        }
    }
}
