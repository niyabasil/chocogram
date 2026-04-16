<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard;

use \Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Magento\Framework\DataObject\IdentityInterface;

class MessageCardStore extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Amasty\GiftWrap\Model\MessageCard\ResourceModel\MessageCardStore::class);
        $this->setIdFieldName(MessageCardInterface::ENTITY_STORE_ID);
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
