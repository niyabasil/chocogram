<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard\ResourceModel;

use Amasty\GiftWrap\Model\MessageCard\MessageCardStore as MessageCardStoreModel;
use Magento\Framework\Model\AbstractModel;
use \Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MessageCardStore extends AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(MessageCardInterface::STORE_TABLE, MessageCardInterface::ENTITY_STORE_ID);
    }

    /**
     * @param MessageCardStoreModel $model
     * @param int $entityId
     * @param int $storeId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByIdAndStore(MessageCardStoreModel $model, int $entityId, int $storeId)
    {
        $connection = $this->getConnection();
        $select = $this->getLoadStoreSelect($entityId, $storeId);
        $data = $connection->fetchRow($select);

        if ($data) {
            $model->setData($data);
        }

        return $this;
    }

    /**
     * @param int $entityId
     * @param int $storeId
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getLoadStoreSelect(int $entityId, int $storeId)
    {
        $storeField = $this->getConnection()->quoteIdentifier(
            sprintf('%s.%s', $this->getMainTable(), MessageCardInterface::STORE_ID)
        );
        $entityField = $this->getConnection()->quoteIdentifier(
            sprintf('%s.%s', $this->getMainTable(), MessageCardInterface::CARD_MESSAGE_ID)
        );
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where($storeField . '=?', $storeId)
            ->where($entityField . '=?', $entityId);

        return $select;
    }
}
