<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper;

use Amasty\GiftWrap\Api\Data\WrapInterface;
use Amasty\GiftWrap\Model\Wrapper\ResourceModel\Collection as WrapCollection;
use Amasty\GiftWrap\Model\Wrapper\ResourceModel\CollectionFactory as WrapCollectionFactory;

class Resolver
{
    /**
     * @var WrapCollectionFactory
     */
    private $wrapCollectionFactory;

    public function __construct(WrapCollectionFactory $wrapCollectionFactory)
    {
        $this->wrapCollectionFactory = $wrapCollectionFactory;
    }

    /**
     * @param int $storeId
     * @param array|null $wrapIds
     * @return array
     */
    public function getSorted($storeId, $wrapIds = null)
    {
        $wrappers = [];
        $defaultCollection = $this->getWrapCollectionByStore($wrapIds);
        $storeCollection = $this->getWrapCollectionByStore($wrapIds, $storeId);
        foreach ($defaultCollection as $wrapper) {
            /** @var Wrap $wrapper */
            $storeModel = $storeCollection->getItemById($wrapper->getEntityId());
            if ($storeModel) {
                $wrapper->addStoreData($storeModel->getData());
            }

            if ($wrapper->getStatus()) {
                $sortOrder = (int)$wrapper->getSortOrder();
                while (true) {
                    if (!isset($wrappers[$sortOrder])) {
                        break;
                    }
                    $sortOrder++;
                }
                $wrappers[$sortOrder] = $wrapper;
            }
        }
        ksort($wrappers);

        return $wrappers;
    }

    /**
     * @param int $storeId
     * @param array|null $wrapIds
     * @return array
     */
    public function getAssoc($storeId, $wrapIds = null)
    {
        $wrappers = [];
        $defaultCollection = $this->getWrapCollectionByStore($wrapIds);
        $storeCollection = $this->getWrapCollectionByStore($wrapIds, $storeId);
        foreach ($defaultCollection as $wrapper) {
            /** @var Wrap $wrapper */
            $storeModel = $storeCollection->getItemById($wrapper->getEntityId());
            if ($storeModel) {
                $wrapper->addStoreData($storeModel->getData());
            }

            if ($wrapper->getStatus()) {
                $wrappers[$wrapper->getId()] = $wrapper;
            }
        }

        return $wrappers;
    }

    /**
     * @param array|null $wrapIds
     * @param int $store
     *
     * @return WrapCollection
     */
    public function getWrapCollectionByStore($wrapIds, $store = 0)
    {
        /** @var WrapCollection $wrappers */
        $wrappers = $this->wrapCollectionFactory->create();
        $wrappers->joinStoreTable($store);
        if ($wrapIds) {
            $wrappers->addFieldToFilter(WrapInterface::ENTITY_ID, $wrapIds);
        }

        return $wrappers;
    }
}
