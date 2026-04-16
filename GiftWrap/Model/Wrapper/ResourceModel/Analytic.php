<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper\ResourceModel;

use Amasty\GiftWrap\Api\Data\WrapInterface;
use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Amasty\GiftWrap\Block\Adminhtml\Wrap\Analytic as BlockAnalytic;
use Magento\Store\Model\Store;

class Analytic extends AbstractDb
{
    public const WRAP_COUNT = 5;
    /**
     * @var string
     */
    private $wrapOrderName;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->wrapOrderName = $this->getTable(SaleDataResourceInterface::ORDER_WRAP_TABLE);
    }

    /**
     * @return array
     */
    public function getMostPopularWraps()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['wrap_order' => $this->wrapOrderName],
                [
                    BlockAnalytic::FIELD_ID => 'wrap_order.wrap_id',
                    BlockAnalytic::FIELD_NAME => 'wrap_store.name',
                    BlockAnalytic::FIELD_QTY => 'count(*)',
                    BlockAnalytic::FIELD_TOTAL => 'sum(wrap_order.base_price)'
                ]
            )->joinLeft(
                ['wrap_store' => $this->getTable(WrapInterface::STORE_TABLE)],
                'wrap_order.wrap_id = wrap_store.wrap_id and wrap_store.store_id = 0',
                []
            )->group(
                ['wrap_order.wrap_id', 'wrap_store.name']
            )->order(
                'qty DESC'
            )->limit(
                self::WRAP_COUNT
            );

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @return array
     */
    public function getStatistics()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['wrap_order' => $this->getTable(SaleDataResourceInterface::ORDER_WRAP_TABLE)],
                [
                    BlockAnalytic::FIELD_ID  => 'wrap_order.wrap_id',
                    BlockAnalytic::FIELD_QTY  => 'count(*)',
                    BlockAnalytic::FIELD_TOTAL  => 'sum(wrap_order.base_price)',
                    BlockAnalytic::FIELD_DATE => 'date(created_at)'
                ]
            )->joinLeft(
                ['sales_order' => $this->getTable('sales_order')],
                'wrap_order.order_id = sales_order.entity_id',
                []
            )->group(
                ['wrap_order.wrap_id', 'date(created_at)']
            )->order(
                'date(created_at) ASC'
            );

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @return array
     */
    public function getWrapNames()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['wrap_store' => $this->getTable(WrapInterface::STORE_TABLE)],
                [
                    WrapInterface::NAME,
                    WrapInterface::WRAP_ID
                ]
            )->where(
                Store::STORE_ID . ' = ?',
                Store::DEFAULT_STORE_ID
            );

        return $this->getConnection()->fetchAll($select);
    }
}
