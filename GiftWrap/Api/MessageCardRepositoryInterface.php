<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Api;

/**
 * @api
 */
interface MessageCardRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\GiftWrap\Api\Data\MessageCardInterface $card
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function save(\Amasty\GiftWrap\Api\Data\MessageCardInterface $card);

    /**
     * Get by id
     *
     * @param int $entityId
     * @param int $storeId
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId, $storeId = 0);

    /**
     * Delete
     *
     * @param \Amasty\GiftWrap\Api\Data\MessageCardInterface $entity
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftWrap\Api\Data\MessageCardInterface $entity);

    /**
     * Duplicate
     *
     * @param int $messageCardId
     *
     * @return bool true on success
     */
    public function duplicate(int $messageCardId);

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
