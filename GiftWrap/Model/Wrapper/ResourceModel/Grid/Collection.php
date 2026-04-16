<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper\ResourceModel\Grid;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Amasty\GiftWrap\Model\Wrapper\ResourceModel\Collection as WrapCollection;
use Amasty\GiftWrap\Api\Data\WrapInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

class Collection extends WrapCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface|null
     */
    protected $aggregations;

    /**
     * @var array
     */
    private $mappedFields = [
        'entity_id' => 'main_table.entity_id',
        'price' => 'store_table.price',
        'qty' => 'count(order_wrap.entity_id)'
    ];

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = Document::class,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
    }

    protected function _construct()
    {
        foreach ($this->mappedFields as $field => $mappedField) {
            $this->addFilterToMap($field, new \Zend_Db_Expr($mappedField));
        }
        parent::_construct();
    }

    /**
     * @return mixed
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * @return null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * @param array|null $items
     * @return $this
     */
    public function setItems(?array $items = null)
    {
        return $this;
    }

    /**
     * Compatibility with m2.1.8 - 2.1.9
     *
     * @param null $limit
     * @param null $offset
     * @return \Magento\Framework\DB\Select
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        $idsSelect->limit($limit, $offset);
        return $idsSelect;
    }

    /**
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreTable();
        $this->joinSaleTable();
        parent::_renderFiltersBefore();
    }

    public function joinSaleTable()
    {
        $orderWrapTable = $this->getResource()->getTable(SaleDataResourceInterface::ORDER_WRAP_TABLE);
        $this->getSelect()->joinLeft(
            ['order_wrap' => $orderWrapTable],
            "order_wrap.wrap_id = main_table.entity_id",
            ['qty' => 'count(order_wrap.entity_id)']
        );
        $this->getSelect()->group('main_table.entity_id');
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return WrapCollection
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }
        return parent::addOrder($field, $direction);
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return WrapCollection
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if (array_key_exists($field, $this->mappedFields)) {
            $field = $this->mappedFields[$field];
        }
        return parent::setOrder($field, $direction);
    }
}
