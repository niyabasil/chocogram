<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Checkout\Cart\Wrap;

use Amasty\GiftWrap\Block\Wrap\Existing\Content;

class ListWrap extends Content
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GiftWrap::checkout/cart/list.phtml';

    /**
     * @var null|array
     */
    private $itemsData = null;

    /**
     * @param int $wrapId
     * @return array
     */
    public function getItemsInfo($wrapId)
    {
        if ($this->itemsData === null) {
            $this->itemsData = $this->getSaleDataModel()->loadItemsDataByWrapIds(array_keys($this->getExistingWraps()));
        }

        return $this->itemsData[$wrapId] ?? [];
    }

    /**
     * @param int $itemId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItemName($itemId)
    {
        $item = $this->getCheckoutSession()->getQuote()->getItemsCollection()->getItemById($itemId);

        return $item ? $item->getName() : '';
    }
}
