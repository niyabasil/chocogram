<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Adminhtml\Message\Card;

use Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Amasty\GiftWrap\Model\MessageCard\MessageCard;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_GiftWrap::message_card';

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $dataObject;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\GiftWrap\Model\MessageCard\MessageCardRepository
     */
    private $messageCardRepository;

    /**
     * @var \Amasty\GiftWrap\Model\MessageCard\MessageCardFactory
     */
    private $messageCardFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\GiftWrap\Model\MessageCard\MessageCardRepository $messageCardRepository,
        \Amasty\GiftWrap\Model\MessageCard\MessageCardFactory $messageCardFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\DataObject $dataObject,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
        $this->messageCardRepository = $messageCardRepository;
        $this->messageCardFactory = $messageCardFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $messageCardId = (int)$this->getRequest()->getParam(MessageCardInterface::ENTITY_ID);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data) {
            /** @var MessageCard $model */
            $model = $this->messageCardFactory->create();

            try {
                if ($messageCardId) {
                    $model = $this->messageCardRepository->getById(
                        $messageCardId,
                        $data[MessageCardInterface::STORE_ID] ?? 0
                    );
                }

                $data = $this->prepareData($data);
                $model->setData($data);
                $this->messageCardRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Gift Message Card was successfully saved.'));
                $this->dataPersistor->clear(MessageCard::PERSIST_NAME);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('amgiftwrap/*/edit', [
                        'id' => $model->getEntityId(),
                        'store' => $data[MessageCardInterface::STORE_ID] ?? 0
                    ]);

                    return $resultRedirect;
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                if ($messageCardId) {
                    $resultRedirect->setPath('amgiftwrap/*/edit', ['id' => $messageCardId]);
                } else {
                    $resultRedirect->setPath('amgiftwrap/*/newAction');
                }

                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the gift message card data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->dataPersistor->set(MessageCard::PERSIST_NAME, $data);
                $resultRedirect->setPath('amgiftwrap/*/edit', ['id' => $messageCardId]);

                return $resultRedirect;
            }
        }

        return $resultRedirect->setPath('amgiftwrap/*/');
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareData($data)
    {
        if (isset($data[MessageCardInterface::ENTITY_ID])) {
            $data[MessageCardInterface::ENTITY_ID] = (int)$data[MessageCardInterface::ENTITY_ID] ?: null;
        }

        $data[MessageCardInterface::IMAGE] = $data[MessageCardInterface::IMAGE][0]['name'] ?? null;
        $data = $this->removeDefaultValues($data);

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function removeDefaultValues($data)
    {
        $default = $data['use_default'] ?? [];
        if ($default) {
            foreach ($default as $key => $value) {
                if ($value === '1') {
                    $data[$key] = null;
                }
            }
        }
        unset($data['use_default']);
        unset($data['entity_store_id']);

        return $data;
    }
}
