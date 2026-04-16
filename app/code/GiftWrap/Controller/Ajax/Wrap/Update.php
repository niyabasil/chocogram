<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Ajax\Wrap;

use Amasty\GiftWrap\Controller\Ajax\AbstractAction;

class Update extends AbstractAction
{
    /**
     * @inheritdoc
     */
    public function action()
    {
        $wrapData = $this->getRequest()->getPost('amwrap', []);
        $quoteWrap = $this->getWrapManagement()->updateWrap($wrapData);

        $this->getCart()->getQuote()->load($this->getCart()->getQuote()->getId());

        $this->updateQuoteItems((int) $quoteWrap->getId());

        // need disable wrap saving after quote saving because wraps already updates by previous sql
//        $this->getCart()->getQuote()->setIsWrapSaveDisabled(true);
        $this->getCart()->getQuote()->collectTotals()->save();

        return $this->getUpdatedBlocks();
    }

    /**
     * @param int $quoteWrapId
     */
    private function updateQuoteItems(int $quoteWrapId)
    {
        $itemQty = $this->getRequest()->getPost('itemQty', []);
        $deleted = array_keys($this->getRequest()->getPost('deleted', []));
        foreach ($this->getCart()->getQuote()->getItemsCollection() as $quoteItem) {
            if (in_array($quoteItem->getItemId(), $deleted)) {
                $this->getWrapManagement()->removeQuoteItem($quoteItem, $quoteWrapId);
            } elseif (isset($itemQty[$quoteItem->getItemId()])) {
                $this->getWrapManagement()->updateQuoteItem(
                    $quoteItem,
                    $quoteWrapId,
                    (float) $itemQty[$quoteItem->getItemId()]
                );
            }
        }
    }
}
