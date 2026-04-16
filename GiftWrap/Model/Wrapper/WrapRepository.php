<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper;

use Amasty\GiftWrap\Api\Data\WrapInterface;
use Amasty\GiftWrap\Api\WrapRepositoryInterface;
use Amasty\GiftWrap\Model\Wrapper\WrapFactory;
use Amasty\GiftWrap\Model\Wrapper\ResourceModel\Wrap as WrapResource;
use Amasty\GiftWrap\Model\Wrapper\ResourceModel\CollectionFactory;
use Amasty\GiftWrap\Model\Wrapper\ResourceModel\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WrapRepository implements WrapRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var WrapFactory
     */
    private $wrapFactory;

    /**
     * @var WrapResource
     */
    private $wrapResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $wraps;

    /**
     * @var CollectionFactory
     */
    private $wrapCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        WrapFactory $wrapFactory,
        WrapResource $wrapResource,
        CollectionFactory $wrapCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->wrapFactory = $wrapFactory;
        $this->wrapResource = $wrapResource;
        $this->wrapCollectionFactory = $wrapCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(WrapInterface $wrap)
    {
        try {
            $this->wrapResource->save($wrap);
            unset($this->wraps[$wrap->getEntityId()]);
        } catch (\Exception $e) {
            if ($wrap->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save wrap with ID %1. Error: %2',
                        [$wrap->getEntityId(), $e->getMessage()]
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
    public function getById($entityId, $storeId = 0)
    {
        if (!isset($this->wraps[$entityId][$storeId])) {
            /** @var \Amasty\GiftWrap\Model\Wrapper\Wrap $wrap */
            $wrap = $this->wrapFactory->create();
            $this->wrapResource->load($wrap, $entityId);
            if ($storeId) {
                $wrap->setStoreId($storeId);
                $this->wrapResource->loadCurrentStoreValue($wrap);
            }
            if (!$wrap->getEntityId()) {
                throw new NoSuchEntityException(__('Wrap with specified ID "%1" not found.', $entityId));
            }
            $this->wraps[$entityId][$storeId] = $wrap;
        }

        return $this->wraps[$entityId][$storeId];
    }

    /**
     * @inheritdoc
     */
    public function delete(WrapInterface $wrap)
    {
        try {
            $this->wrapResource->delete($wrap);
            unset($this->wraps[$wrap->getEntityId()]);
        } catch (\Exception $e) {
            if ($wrap->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove wrap with ID %1. Error: %2',
                        [$wrap->getEntityId(), $e->getMessage()]
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
    public function duplicate($wrapId)
    {
        $newWrapId = $this->createNewWrap();
        if ($newWrapId) {
            $this->wrapResource->duplicateStoreData($wrapId, $newWrapId);
        }

        return true;
    }

    /**
     * @return int|mixed
     */
    protected function createNewWrap()
    {
        /** @var \Amasty\GiftWrap\Model\Wrapper\Wrap $wrap */
        $wrap = $this->wrapFactory->create();
        $wrap->setOrigData('skip_after_save', true);
        $wrap->setData('skip_after_save', true);
        $this->wrapResource->save($wrap);

        return $wrap->getEntityId();
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $wrapModel = $this->getById($entityId);
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

        /** @var \Amasty\GiftWrap\Model\Wrapper\ResourceModel\Collection $wrapCollection */
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
            $wraps[] = $this->getById($wrap->getEntityId());
        }
        
        $searchResults->setItems($wraps);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $wrapCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $wrapCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $wrapCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     * @param $sortOrders
     * @param Collection $wrapCollection
     */
    private function addOrderToCollection($sortOrders, Collection $wrapCollection)
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
