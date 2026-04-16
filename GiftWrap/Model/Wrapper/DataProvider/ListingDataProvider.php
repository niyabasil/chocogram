<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class ListingDataProvider extends DataProvider
{
    /**
     * @var array
     */
    private $mappedFields = [
        'entity_id' => 'main_table.entity_id'
    ];

    /**
     * @var array
     */
    private $havingColumns = [
        'qty' => 'count(order_wrap.entity_id)'
    ];

    /**
     * @var array
     */
    private $havingFilters = [];

    /**
     * @param Filter $filter
     * @return mixed|void
     */
    public function addFilter(Filter $filter)
    {
        if (array_key_exists($filter->getField(), $this->mappedFields)) {
            $mappedField = $this->mappedFields[$filter->getField()];
            $filter->setField($mappedField);
        } elseif (array_key_exists($filter->getField(), $this->havingColumns)) {
            $filter->setField($this->havingColumns[$filter->getField()]);
            $this->havingFilters[] = $filter;
            return;
        }

        parent::addFilter($filter);
    }

    /**
     * @inheritdoc
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $operations = [
            'gteq' => '>=',
            'lteq' => '<=',
            'like' => 'like'
        ];

        foreach ($this->havingFilters as $filter) {
            $searchResult->getSelect()->having(
                $filter->getField() . ' ' . $operations[$filter->getConditionType()] . ' "' . $filter->getValue() . '"'
            );
        }

        return parent::searchResultToOutput($searchResult);
    }
}
