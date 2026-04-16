<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard\DataProvider;

use Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Magento\Framework\App\RequestInterface;
use Amasty\GiftWrap\Model\MessageCard\MessageCard;
use Magento\Framework\App\Request\DataPersistorInterface;
use Amasty\GiftWrap\Model\MessageCard\ResourceModel\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Amasty\GiftWrap\Api\MessageCardRepositoryInterface;

class FormDataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var MessageCardRepositoryInterface
     */
    private $messageCardRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\GiftWrap\Model\ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        PoolInterface $pool,
        MessageCardRepositoryInterface $messageCardRepository,
        RequestInterface $request,
        \Amasty\GiftWrap\Model\ImageProcessor $imageProcessor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        $this->pool = $pool;
        $this->messageCardRepository = $messageCardRepository;
        $this->request = $request;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = parent::getData() ?: [];

        /** @var MessageCardInterface $current */
        $current = $this->coreRegistry->registry(MessageCard::PERSIST_NAME);
        if ($current && $current->getEntityId()) {
            $data = $current->getData();
            $result[$current->getEntityId()] = $this->prepareData($data);
        } else {
            $data = $this->dataPersistor->get(MessageCard::PERSIST_NAME);
            if (!empty($data)) {
                /** @var MessageCardInterface $messageCard */
                $messageCard = $this->collection->getNewEmptyItem();
                $messageCard->setData($data);
                $data = $messageCard->getData();
                $result[$messageCard->getEntityId()] = $this->prepareData($data);
                $this->dataPersistor->clear(MessageCard::PERSIST_NAME);
            }
        }

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $result = $modifier->modifyData($result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * @param $data
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function prepareData($data)
    {
        if (isset($data[MessageCardInterface::IMAGE]) && $data[MessageCardInterface::IMAGE]) {
            $data[MessageCardInterface::IMAGE] = [
                [
                    'name' => $data[MessageCardInterface::IMAGE],
                    'url'  => $this->imageProcessor->getThumbnailUrl($data[MessageCardInterface::IMAGE])
                ]
            ];
        }

        return $data;
    }
}
