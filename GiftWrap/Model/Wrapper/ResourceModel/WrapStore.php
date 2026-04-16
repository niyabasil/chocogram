<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use \Amasty\GiftWrap\Api\Data\WrapInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class WrapStore extends AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(WrapInterface::STORE_TABLE, WrapInterface::ENTITY_STORE_ID);
    }

    /**
     * @param \Amasty\GiftWrap\Model\Wrapper\WrapStore $model
     * @param int $entityId
     * @param int $storeId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByIdAndStore(\Amasty\GiftWrap\Model\Wrapper\WrapStore $model, int $entityId, int $storeId)
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
            sprintf('%s.%s', $this->getMainTable(), WrapInterface::STORE_ID)
        );
        $entityField = $this->getConnection()->quoteIdentifier(
            sprintf('%s.%s', $this->getMainTable(), WrapInterface::WRAP_ID)
        );
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where($storeField . '=?', $storeId)
            ->where($entityField . '=?', $entityId);
        return $select;
    }
}
