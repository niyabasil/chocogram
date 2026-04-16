<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper;

use \Amasty\GiftWrap\Api\Data\WrapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class WrapStore extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Amasty\GiftWrap\Model\Wrapper\ResourceModel\WrapStore::class);
        $this->setIdFieldName(WrapInterface::ENTITY_STORE_ID);
    }

    /**
     * @param int $entityId
     * @param int $storeId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByIdAndStore(int $entityId, int $storeId)
    {
        $this->_getResource()->loadByIdAndStore($this, $entityId, $storeId);
        return $this;
    }
}
