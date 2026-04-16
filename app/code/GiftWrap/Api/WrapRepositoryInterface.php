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
interface WrapRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\GiftWrap\Api\Data\WrapInterface $wrap
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function save(\Amasty\GiftWrap\Api\Data\WrapInterface $wrap);

    /**
     * Get by id
     *
     * @param int $entityId
     * @param int $storeId
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId, $storeId = 0);

    /**
     * Delete
     *
     * @param \Amasty\GiftWrap\Api\Data\WrapInterface $wrap
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftWrap\Api\Data\WrapInterface $wrap);

    /**
     * Duplicate
     *
     * @param int $wrapId
     *
     * @return bool true on success
     */
    public function duplicate($wrapId);

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
     * @return \Amasty\GiftWrap\Api\Data\WrapSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
