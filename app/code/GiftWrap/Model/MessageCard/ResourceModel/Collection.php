<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName(MessageCardInterface::ENTITY_ID);
        $this->_init(
            \Amasty\GiftWrap\Model\MessageCard\MessageCard::class,
            \Amasty\GiftWrap\Model\MessageCard\ResourceModel\MessageCard::class
        );
    }

    /**
     * @param null|int $storeId
     */
    public function joinStoreTable($storeId = null)
    {
        $storeId = (int) $storeId;
        $storeTable = $this->getResource()->getTable(MessageCardInterface::STORE_TABLE);
        $this->getSelect()->joinInner(
            $storeTable,
            "{$storeTable}.message_card_id = main_table.entity_id AND {$storeTable}.store_id = {$storeId}"
        );
    }
}
