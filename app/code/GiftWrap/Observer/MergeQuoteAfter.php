<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class MergeQuoteAfter implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $oldQuote */
        $oldQuote = $observer->getSource();
        /** @var Quote $newQuote */
        $newQuote = $observer->getQuote();
        if ($oldQuote && $newQuote) {
            $oldQuoteWraps = $oldQuote->getWrapItems();
            $newQuoteWraps = $newQuote->getWrapItems();
            if (!empty($oldQuoteWraps)) {
                foreach ($oldQuoteWraps as $oldQuoteWrap) {
                    $newWrap = clone $oldQuoteWrap;
                    $newWrap->unsQuoteId();
                    $newQuoteWraps[] = $newWrap;
                }
            }
            $newQuote->setWrapItems($newQuoteWraps);
        }
    }
}
