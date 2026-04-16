<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard\ResourceModel\MessageCardStore;

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
        $this->_setIdFieldName(MessageCardInterface::ENTITY_STORE_ID);
        $this->_init(
            \Amasty\GiftWrap\Model\MessageCard\MessageCardStore::class,
            \Amasty\GiftWrap\Model\MessageCard\ResourceModel\MessageCardStore::class
        );
    }
}
