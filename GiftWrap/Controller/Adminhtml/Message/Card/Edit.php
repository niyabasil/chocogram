<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Adminhtml\Message\Card;

use Amasty\GiftWrap\Model\MessageCard\MessageCard;
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
    public const ADMIN_RESOURCE = 'Amasty_GiftWrap::message_card';

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\GiftWrap\Api\MessageCardRepositoryInterface
     */
    private $messageCardRepository;

    /**
     * @var \Amasty\GiftWrap\Model\MessageCard\MessageCardFactory
     */
    private $messageCardFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\GiftWrap\Api\MessageCardRepositoryInterface $messageCardRepository,
        \Amasty\GiftWrap\Model\MessageCard\MessageCardFactory $messageCardFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
        $this->messageCardRepository = $messageCardRepository;
        $this->messageCardFactory = $messageCardFactory;
    }

    public function execute()
    {
        $messageCardId = (int)$this->getRequest()->getParam('id');
        $storeId = (int)$this->getRequest()->getParam('store');
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($messageCardId) {
            try {
                $model = $this->messageCardRepository->getById($messageCardId, $storeId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Gift Message Card no longer exists.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

                return $resultRedirect->setPath('*/*/index');
            }
        } else {
            /** @var MessageCard $model */
            $model = $this->messageCardFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->dataPersistor->get(MessageCard::PERSIST_NAME);
        if (!empty($data) && !$model->getEntityId()) {
            $model->addData($data);
        }

        $this->coreRegistry->register(MessageCard::PERSIST_NAME, $model);
        $this->initAction($resultPage, $model);

        // set title and breadcrumbs
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Gift Message Cards'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getEntityId() ?
                __('Edit Gift Message Card # %1', $model->getEntityId())
                : __('New Gift Message Card')
        );

        return $resultPage;
    }

    private function initAction(ResultInterface $resultPage, MessageCard $model): void
    {
        $breadcrumb = $model->getEntityId() ?
            __('Edit Gift Message Card # %1', $model->getEntityId())
            : __('Gift Message Card');
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
    }
}
