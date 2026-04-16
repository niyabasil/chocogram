<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Adminhtml\Wrap;

use Amasty\GiftWrap\Model\Wrapper\Wrap;
use Amasty\GiftWrap\Api\Data\WrapInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_GiftWrap::wrap';

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\GiftWrap\Api\WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var \Amasty\GiftWrap\Model\Wrapper\WrapFactory
     */
    private $wrapFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\GiftWrap\Api\WrapRepositoryInterface $wrapRepository,
        \Amasty\GiftWrap\Model\Wrapper\WrapFactory $wrapFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
        $this->wrapRepository = $wrapRepository;
        $this->wrapFactory = $wrapFactory;
    }

    public function execute()
    {
        $wrapId = (int)$this->getRequest()->getParam('id');
        $storeId = (int)$this->getRequest()->getParam('store');
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($wrapId) {
            try {
                $model = $this->wrapRepository->getById($wrapId, $storeId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Wrap no longer exists.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                return $resultRedirect->setPath('*/*/index');
            }
        } else {
            /** @var Wrap $model */
            $model = $this->wrapFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->dataPersistor->get(Wrap::PERSIST_NAME);
        if (!empty($data) && !$model->getEntityId()) {
            $model->addData($data);
        }

        $this->coreRegistry->register(Wrap::PERSIST_NAME, $model);
        $this->initAction($resultPage, $model);
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getEntityId() ?
                __('Edit Gift Wrap # %1', $model->getEntityId())
                : __('New Gift Wrap')
        );

        return $resultPage;
    }

    private function initAction(ResultInterface $resultPage, WrapInterface $model): void
    {
        $breadcrumb = $model->getEntityId() ?
            __('Edit Gift Wrap # %1', $model->getEntityId())
            : __('Gift Wrap');
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
    }
}
