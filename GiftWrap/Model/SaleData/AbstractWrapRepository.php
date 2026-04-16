<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Api\SaleData\WrapRepositoryInterface;
use Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap as AddressWrapResource;
use Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap\Collection as AddressWrapCollection;
use Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap\CollectionFactory as AddressWrapCollectionFactory;
use Amasty\GiftWrap\Model\SaleData\Address\Wrap as AddressWrap;
use Amasty\GiftWrap\Model\SaleData\Address\WrapFactory as AddressWrapFactory;
use Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap as QuoteWrapResource;
use Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap\CollectionFactory as QuoteWrapCollectionFactory;
use Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap\Collection as QuoteWrapCollection;
use Amasty\GiftWrap\Model\SaleData\Quote\Wrap as QuoteWrap;
use Amasty\GiftWrap\Model\SaleData\Quote\WrapFactory as QuoteWrapFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

abstract class AbstractWrapRepository implements WrapRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var QuoteWrapFactory|AddressWrapFactory
     */
    protected $wrapFactory;

    /**
     * @var QuoteWrapResource|AddressWrapResource
     */
    protected $wrapResource;

    /**
     * Model data storage
     *
     * @var array
     */
    protected $wraps;

    /**
     * @var QuoteWrapCollectionFactory|AddressWrapCollectionFactory\
     */
    protected $wrapCollectionFactory;

    /**
     * @inheritdoc
     */
    public function save(WrapInterface $wrap)
    {
        try {
            if ($wrap->getId()) {
                $wrap = $this->getById($wrap->getId())->addData($wrap->getData());
            }
            $this->wrapResource->save($wrap);
            unset($this->wraps[$wrap->getId()]);
            $this->wraps[$wrap->getId()] = $wrap;
        } catch (\Exception $e) {
            if ($wrap->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save wrap with ID %1. Error: %2',
                        [$wrap->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new wrap. Error: %1', $e->getMessage()));
        }

        return $wrap;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->wraps[$id])) {
            /** @var QuoteWrap|AddressWrap $wrap */
            $wrap = $this->wrapFactory->create();
            $this->wrapResource->load($wrap, $id);
            if (!$wrap->getId()) {
                throw new NoSuchEntityException(__('Wrap with specified ID "%1" not found.', $id));
            }
            $this->wraps[$id] = $wrap;
        }

        return $this->wraps[$id];
    }

    /**
     * @return WrapInterface
     */
    public function getNewItem()
    {
        return $this->wrapFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function delete(WrapInterface $wrap)
    {
        try {
            $this->wrapResource->delete($wrap);
            unset($this->wraps[$wrap->getId()]);
        } catch (\Exception $e) {
            if ($wrap->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove wrap with ID %1. Error: %2',
                        [$wrap->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove wrap. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $wrapModel = $this->getById($id);
        $this->delete($wrapModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $wrapCollection */
        $wrapCollection = $this->wrapCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $wrapCollection);
        }

        $searchResults->setTotalCount($wrapCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $wrapCollection);
        }

        $wrapCollection->setCurPage($searchCriteria->getCurrentPage());
        $wrapCollection->setPageSize($searchCriteria->getPageSize());

        $wraps = [];
        /** @var WrapInterface $wrap */
        foreach ($wrapCollection->getItems() as $wrap) {
            $wraps[] = $this->getById($wrap->getId());
        }

        $searchResults->setItems($wraps);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param QuoteWrapCollection|AddressWrapCollection  $wrapCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, $wrapCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $wrapCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param QuoteWrapCollection|AddressWrapCollection  $wrapCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, $wrapCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $wrapCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
