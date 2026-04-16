<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Amasty\GiftWrap\Api\Data\WrapInterface;
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
        $this->_setIdFieldName(WrapInterface::ENTITY_ID);
        $this->_init(
            \Amasty\GiftWrap\Model\Wrapper\Wrap::class,
            \Amasty\GiftWrap\Model\Wrapper\ResourceModel\Wrap::class
        );
    }

    /**
     * @param null|int $storeId
     */
    public function joinStoreTable($storeId = null)
    {
        $storeId = (int) $storeId;
        $storeTable = $this->getResource()->getTable(WrapInterface::STORE_TABLE);
        $this->getSelect()->joinInner(
            ['store_table' => $storeTable],
            "store_table.wrap_id = main_table.entity_id AND store_table.store_id = {$storeId}"
        );
    }
}
