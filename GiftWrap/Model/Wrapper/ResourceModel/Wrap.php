<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper\ResourceModel;

use Amasty\GiftWrap\Model\Wrapper\Wrap as WrapModel;
use Amasty\GiftWrap\Model\Wrapper\WrapStoreFactory;
use Amasty\GiftWrap\Model\Wrapper\WrapStore as WrapStoreModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use \Amasty\GiftWrap\Api\Data\WrapInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Wrap extends AbstractDb
{
    /**
     * @var WrapStoreFactory
     */
    private $wrapStoreFactory;

    /**
     * @var \Amasty\GiftWrap\Model\ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var WrapStore\CollectionFactory
     */
    private $wrapStoreCollectionFactory;

    public function __construct(
        Context $context,
        WrapStoreFactory $wrapStoreFactory,
        \Amasty\GiftWrap\Model\ImageProcessor $imageProcessor,
        \Amasty\GiftWrap\Model\Wrapper\ResourceModel\WrapStore\CollectionFactory $wrapStoreCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->wrapStoreFactory = $wrapStoreFactory;
        $this->imageProcessor = $imageProcessor;
        $this->wrapStoreCollectionFactory = $wrapStoreCollectionFactory;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(WrapInterface::MAIN_TABLE, WrapInterface::ENTITY_ID);
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $skip = $object->getOrigData('skip_after_save');
        if (!$skip) {
            $this->saveStoreData($object);
            $this->saveImage($object);
            $this->checkOldImage($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->loadStoreData($object);
        return parent::_afterLoad($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function checkOldImage(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getOrigData(WrapInterface::IMAGE)
            && $object->getOrigData(WrapInterface::IMAGE) != $object->getData(WrapInterface::IMAGE)
            && $object->getOrigData(WrapInterface::STORE_ID) == $object->getData(WrapInterface::STORE_ID)
        ) {
            $this->imageProcessor->deleteImage($object->getOrigData(WrapInterface::IMAGE));
        }
    }

    /**
     * @param WrapModel $object
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveImage(WrapModel $object)
    {
        $image = $object->getData(WrapInterface::IMAGE);
        if ($image) {
            $this->imageProcessor->saveImage($image);
        }
    }

    /**
     * @param WrapModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveStoreData(WrapModel $object)
    {
        /** @var WrapStoreModel $storeModel */
        $storeModel = $this->wrapStoreFactory->create();
        $object->setWrapId($object->getEntityId());

        if ($object->getStoreId()) {
            $storeModelCheck = $this->wrapStoreFactory->create()
                ->loadByIdAndStore((int)$object->getEntityId(), 0);
            if (!$storeModelCheck->getEntityStoreId()) {
                throw new LocalizedException(
                    __('There are not all store view data. Please create item with store = 0 before.')
                );
            }
        }

        if ($object->getEntityId()) {
            $storeModel->loadByIdAndStore((int)$object->getEntityId(), (int)$object->getStoreId());
        }

        $storeModel->addData($object->getData());
        $storeModel->save();
    }

    /**
     * @param WrapModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadStoreData(WrapModel $object)
    {
        $object->setWrapId($object->getEntityId());
        if ($object->getEntityId()) {
            $this->loadDefaultStoreValue($object);
            $this->loadCurrentStoreValue($object);
        }
    }

    /**
     * @param WrapModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadDefaultStoreValue(WrapModel $object)
    {
        /** @var WrapStoreModel $storeModel */
        $storeModel = $this->wrapStoreFactory->create();
        $storeModel->loadByIdAndStore((int)$object->getEntityId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $object->addData($storeModel->getData());
    }

    /**
     * @param WrapModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCurrentStoreValue(WrapModel $object)
    {
        if ($object->getStoreId()) {
            /** @var WrapStoreModel $storeModel */
            $storeModel = $this->wrapStoreFactory->create();
            $storeModel->loadByIdAndStore((int)$object->getEntityId(), (int)$object->getStoreId());
            $object->addStoreData($storeModel->getData());
        }
    }

    /**
     * @param int $wrapId
     * @param int $newWrapId
     */
    public function duplicateStoreData(int $wrapId, int $newWrapId)
    {
        $collection = $this->wrapStoreCollectionFactory->create()
            ->addFieldToFilter(WrapInterface::WRAP_ID, $wrapId);

        foreach ($collection as $item) {
            $item->setWrapId($newWrapId);
            $item->setEntityStoreId(null);
            $item->setName(__('Copy of ') . $item->getName());
            $item->setStatus(0);
            if ($item->getImage()) {
                $item->setImage($this->imageProcessor->copy($item->getImage()));
            }
        }

        $collection->save();
    }
}
