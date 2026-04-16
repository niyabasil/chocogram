<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper\ResourceModel\WrapStore;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use \Amasty\GiftWrap\Api\Data\WrapInterface;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName(WrapInterface::ENTITY_STORE_ID);
        $this->_init(
            \Amasty\GiftWrap\Model\Wrapper\WrapStore::class,
            \Amasty\GiftWrap\Model\Wrapper\ResourceModel\WrapStore::class
        );
    }
}
