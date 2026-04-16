<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Api\SaleData;

/**
 * @api
 */
interface WrapRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\GiftWrap\Api\SaleData\WrapInterface $wrap
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function save(\Amasty\GiftWrap\Api\SaleData\WrapInterface $wrap);

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @return \Amasty\GiftWrap\Api\SaleData\WrapInterface
     */
    public function getNewItem();

    /**
     * Delete
     *
     * @param \Amasty\GiftWrap\Api\SaleData\WrapInterface $wrap
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftWrap\Api\SaleData\WrapInterface $wrap);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
