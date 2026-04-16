<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard\ResourceModel;

use Amasty\GiftWrap\Model\MessageCard\MessageCard as MessageCardModel;
use Amasty\GiftWrap\Model\MessageCard\MessageCardStoreFactory;
use Amasty\GiftWrap\Model\MessageCard\MessageCardStore as MessageCardStoreModel;
use Amasty\GiftWrap\Model\MessageCard\ResourceModel\MessageCardStore\CollectionFactory as CardCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use \Amasty\GiftWrap\Api\Data\MessageCardInterface as CardInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class MessageCard extends AbstractDb
{
    /**
     * @var MessageCardStoreFactory
     */
    private $messageCardStoreFactory;

    /**
     * @var \Amasty\GiftWrap\Model\ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var MessageCardStore\CollectionFactory
     */
    private $messageCardCollectionFactory;

    public function __construct(
        Context $context,
        MessageCardStoreFactory $messageCardStoreFactory,
        \Amasty\GiftWrap\Model\ImageProcessor $imageProcessor,
        CardCollectionFactory $messageCardCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->messageCardStoreFactory = $messageCardStoreFactory;
        $this->imageProcessor = $imageProcessor;
        $this->messageCardCollectionFactory = $messageCardCollectionFactory;
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CardInterface::MAIN_TABLE, CardInterface::ENTITY_ID);
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
        if ($object->getOrigData(CardInterface::IMAGE)
            && $object->getOrigData(CardInterface::IMAGE) != $object->getData(CardInterface::IMAGE)
            && $object->getOrigData(CardInterface::STORE_ID) == $object->getData(CardInterface::STORE_ID)
        ) {
            $this->imageProcessor->deleteImage($object->getOrigData(CardInterface::IMAGE));
        }
    }

    /**
     * @param MessageCardModel $object
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveImage(MessageCardModel $object)
    {
        $image = $object->getData(CardInterface::IMAGE);
        if ($image) {
            $this->imageProcessor->saveImage($image);
        }
    }

    /**
     * @param MessageCardModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function saveStoreData(MessageCardModel $object)
    {
        /** @var MessageCardStoreModel $storeModel */
        $storeModel = $this->messageCardStoreFactory->create();
        $object->setData(CardInterface::CARD_MESSAGE_ID, $object->getEntityId());

        if ($object->getStoreId()) {
            $storeModelCheck = $this->messageCardStoreFactory->create()
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
     * @param MessageCardModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadStoreData(MessageCardModel $object)
    {
        $object->setData(CardInterface::CARD_MESSAGE_ID, $object->getEntityId());
        if ($object->getEntityId()) {
            $this->loadDefaultStoreValue($object);
            $this->loadCurrentStoreValue($object);
        }
    }

    /**
     * @param MessageCardModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadDefaultStoreValue(MessageCardModel $object)
    {
        /** @var MessageCardStoreModel $storeModel */
        $storeModel = $this->messageCardStoreFactory->create();
        $storeModel->loadByIdAndStore((int)$object->getEntityId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $object->addData($storeModel->getData());
    }

    /**
     * @param MessageCardModel $object
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCurrentStoreValue(MessageCardModel $object)
    {
        if ($object->getStoreId()) {
            /** @var MessageCardStoreModel $storeModel */
            $storeModel = $this->messageCardStoreFactory->create();
            $storeModel->loadByIdAndStore((int)$object->getEntityId(), (int)$object->getStoreId());
            $object->addStoreData($storeModel->getData());
        }
    }

    /**
     * @param int $cardId
     * @param int $newCardId
     */
    public function duplicateStoreData(int $cardId, int $newCardId)
    {
        $collection = $this->messageCardCollectionFactory->create()
            ->addFieldToFilter(CardInterface::CARD_MESSAGE_ID, $cardId);

        foreach ($collection as $item) {
            $item->setMessageCardId($newCardId);
            $item->setEntityStoreId(null);
            $item->setName(__('Copy of ') . $item->getName());
            $item->setStatus(0);
            $item->setImage($this->imageProcessor->copy($item->getImage()));
        }

        $collection->save();
    }
}
