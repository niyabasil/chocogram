<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Ajax\Wrap;

use Amasty\GiftWrap\Controller\Ajax\AbstractAction;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Add extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $itemsIdsToUpdate = $this->getRequest()->getPost('itemsIds', []);

        if (!empty($itemsIdsToUpdate)) {
            $wrapData = $this->getRequest()->getPost('amwrap', []);
            $itemsQty = $this->getRequest()->getPost('itemQty', []);
            /** @var QuoteItem $quoteItem */
            foreach ($this->getCart()->getQuote()->getItemsCollection() as $quoteItem) {
                if (in_array($quoteItem->getId(), $itemsIdsToUpdate)) {
                    $quoteWrap = $this->getWrapManagement()->wrapQuoteItem(
                        $quoteItem,
                        $itemsQty[$quoteItem->getId()] ?? null,
                        $wrapData
                    );
                    $wrapData['existing_wrap_id'] = $quoteWrap->getId();
                }
            }
            $this->getCart()->save();
        }

        return $this->getUpdatedBlocks();
    }
}
