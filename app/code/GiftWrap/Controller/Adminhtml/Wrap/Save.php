<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Adminhtml\Wrap;

use Amasty\GiftWrap\Api\Data\WrapInterface;
use Amasty\GiftWrap\Model\Wrapper\Wrap;
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
    public const ADMIN_RESOURCE = 'Amasty_GiftWrap::wrap';

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
     * @var \Amasty\GiftWrap\Model\Wrapper\WrapRepository
     */
    private $wrapRepository;

    /**
     * @var \Amasty\GiftWrap\Model\Wrapper\WrapFactory
     */
    private $wrapFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\GiftWrap\Model\Wrapper\WrapRepository $wrapRepository,
        \Amasty\GiftWrap\Model\Wrapper\WrapFactory $wrapFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\DataObject $dataObject,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
        $this->wrapRepository = $wrapRepository;
        $this->wrapFactory = $wrapFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $wrapId = (int)$this->getRequest()->getParam(WrapInterface::ENTITY_ID);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($data) {
            /** @var Wrap $model */
            $model = $this->wrapFactory->create();

            try {
                if ($wrapId) {
                    $model = $this->wrapRepository->getById($wrapId, $data[WrapInterface::STORE_ID] ?? 0);
                }

                $data = $this->prepareData($data);
                $model->setData($data);
                $this->wrapRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Gift Wrap was successfully saved.'));
                $this->dataPersistor->clear(Wrap::PERSIST_NAME);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('amgiftwrap/*/edit', [
                        'id' => $model->getEntityId(),
                        'store' => $data[WrapInterface::STORE_ID] ?? 0
                    ]);

                    return $resultRedirect;
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                if ($wrapId) {
                    $resultRedirect->setPath('amgiftwrap/*/edit', ['id' => $wrapId]);
                } else {
                    $resultRedirect->setPath('amgiftwrap/*/newAction');
                }

                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the gift wrap data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->dataPersistor->set(Wrap::PERSIST_NAME, $data);
                $resultRedirect->setPath('amgiftwrap/*/edit', ['id' => $wrapId]);

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
        if (isset($data[WrapInterface::ENTITY_ID])) {
            $data[WrapInterface::ENTITY_ID] = (int)$data[WrapInterface::ENTITY_ID] ?: null;
        }

        $data[WrapInterface::IMAGE] = $data[WrapInterface::IMAGE][0]['name'] ?? null;
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
