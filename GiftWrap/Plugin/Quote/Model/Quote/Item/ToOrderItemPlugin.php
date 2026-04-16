<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\Quote\Item;

use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item\ToOrderItem;

class ToOrderItemPlugin
{
    /**
     * @param ToOrderItem $subject
     * @param callable $proceed
     * @param $item
     * @param array $data
     * @return mixed
     */
    public function aroundConvert(ToOrderItem $subject, callable $proceed, $item, $data = [])
    {
        $orderItem = $proceed($item, $data);
        if ($item->getProduct()->isVirtual() || $item->getParentItem() || !$item->getWrapItems()) {
            return $orderItem;
        }

        $wrapItems = [];
        foreach ($item->getWrapItems() as $wrapItem) {
            $wrapData = $wrapItem->getData();
            unset($wrapData['am_gift_wrap_quote_item_id']);
            $wrapItems[] = new DataObject($wrapData);
        }

        $orderItem->setWrapItems($wrapItems);
        return $orderItem;
    }
}
