<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Adminhtml\Wrap;

use Amasty\GiftWrap\Model\Wrapper\WrapRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_GiftWrap::wrap';

    /**
     * @var WrapRepository
     */
    private $wrapRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        WrapRepository $wrapRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->wrapRepository = $wrapRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $wrapId = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($wrapId) {
            try {
                $this->wrapRepository->deleteById($wrapId);
                $this->messageManager->addSuccessMessage(__('The wrap have been deleted.'));

                return $resultRedirect->setPath('amgiftwrap/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete wrap right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $resultRedirect->setPath('amgiftwrap/*/edit', ['id' => $wrapId]);

                return $resultRedirect;
            }
        }

        return $resultRedirect->setPath('amgiftwrap/*/');
    }
}
