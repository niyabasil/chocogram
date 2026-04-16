<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard;

use Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Amasty\GiftWrap\Api\MessageCardRepositoryInterface;
use Amasty\GiftWrap\Model\MessageCard\MessageCardFactory;
use Amasty\GiftWrap\Model\MessageCard\ResourceModel\MessageCard as MessageCardResource;
use Amasty\GiftWrap\Model\MessageCard\ResourceModel\CollectionFactory;
use Amasty\GiftWrap\Model\MessageCard\ResourceModel\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

class MessageCardRepository implements MessageCardRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var MessageCardFactory
     */
    private $messageCardFactory;

    /**
     * @var MessageCardResource
     */
    private $messageCardResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $messageCards;

    /**
     * @var CollectionFactory
     */
    private $messageCardCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        MessageCardFactory $messageCardFactory,
        MessageCardResource $messageCardResource,
        CollectionFactory $messageCardCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->messageCardFactory = $messageCardFactory;
        $this->messageCardResource = $messageCardResource;
        $this->messageCardCollectionFactory = $messageCardCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(MessageCardInterface $messageCard)
    {
        try {
            $this->messageCardResource->save($messageCard);
            unset($this->messageCards[$messageCard->getEntityId()]);
        } catch (\Exception $e) {
            if ($messageCard->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save message card with ID %1. Error: %2',
                        [$messageCard->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new message card. Error: %1', $e->getMessage()));
        }

        return $messageCard;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId, $storeId = 0)
    {
        if (!isset($this->messageCards[$entityId][$storeId])) {
            /** @var \Amasty\GiftWrap\Model\MessageCard\MessageCard $messageCard */
            $messageCard = $this->messageCardFactory->create();
            $this->messageCardResource->load($messageCard, $entityId);
            if ($storeId) {
                $messageCard->setStoreId($storeId);
                $this->messageCardResource->loadCurrentStoreValue($messageCard);
            }
            if (!$messageCard->getEntityId()) {
                throw new NoSuchEntityException(__('Message Card with specified ID "%1" not found.', $entityId));
            }
            $this->messageCards[$entityId][$storeId] = $messageCard;
        }

        return $this->messageCards[$entityId][$storeId];
    }

    /**
     * @inheritdoc
     */
    public function delete(MessageCardInterface $messageCard)
    {
        try {
            $this->messageCardResource->delete($messageCard);
            unset($this->messageCards[$messageCard->getEntityId()]);
        } catch (\Exception $e) {
            if ($messageCard->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove message card with ID %1. Error: %2',
                        [$messageCard->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove  message card. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function duplicate(int $messageCardId)
    {
        $newMessageCardId = (int)$this->createNewMessageCard();
        if ($newMessageCardId) {
            $this->messageCardResource->duplicateStoreData($messageCardId, $newMessageCardId);
        }

        return true;
    }

    /**
     * @return int|mixed
     */
    protected function createNewMessageCard()
    {
        /** @var \Amasty\GiftWrap\Model\MessageCard\MessageCard $messageCard */
        $messageCard = $this->messageCardFactory->create();
        $messageCard->setOrigData('skip_after_save', true);
        $messageCard->setData('skip_after_save', true);
        $this->messageCardResource->save($messageCard);

        return $messageCard->getEntityId();
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $messageCardModel = $this->getById($entityId);
        $this->delete($messageCardModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\GiftWrap\Model\MessageCard\ResourceModel\Collection $messageCardCollection */
        $messageCardCollection = $this->messageCardCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $messageCardCollection);
        }
        
        $searchResults->setTotalCount($messageCardCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $messageCardCollection);
        }
        
        $messageCardCollection->setCurPage($searchCriteria->getCurrentPage());
        $messageCardCollection->setPageSize($searchCriteria->getPageSize());
        
        $messageCards = [];
        /** @var MessageCardInterface $messageCard */
        foreach ($messageCardCollection->getItems() as $messageCard) {
            $messageCards[] = $this->getById($messageCard->getEntityId());
        }
        
        $searchResults->setItems($messageCards);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $messageCardCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $messageCardCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $messageCardCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $messageCardCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $messageCardCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $messageCardCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
