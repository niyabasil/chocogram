<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Processor;

class ProcessorPlugin
{
    /**
     * @param Processor $subject
     * @param Item $result
     * @param Item $source
     * @param Item $target
     * @return Item
     */
    public function afterMerge(Processor $subject, Item $result, Item $source, Item $target)
    {
        $oldWrapItems = $source->getWrapItems();
        $newWrapItems = $target->getWrapItems();
        foreach ($oldWrapItems as $oldWrapItem) {
            $newWrapItem = clone $oldWrapItem;
            $newWrapItem->unsQuoteItemId();
            $newWrapItem->unsEntityId();
            $newWrapItems[] = $newWrapItem;
        }
        $target->setWrapItems($newWrapItems);

        return $result;
    }
}
