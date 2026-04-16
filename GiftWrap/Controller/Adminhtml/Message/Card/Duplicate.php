<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Adminhtml\Message\Card;

use Amasty\GiftWrap\Model\MessageCard\MessageCardRepository;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Duplicate extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_GiftWrap::message_card';

    /**
     * @var MessageCardRepository
     */
    private $messageCardRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        MessageCardRepository $messageCardRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->messageCardRepository = $messageCardRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $messageCardId = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($messageCardId) {
            try {
                $this->messageCardRepository->duplicate($messageCardId);
                $this->messageManager->addSuccessMessage(__('The message card have been duplicated.'));

                return $resultRedirect->setPath('amgiftwrap/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t duplicate message card right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $resultRedirect->setPath('amgiftwrap/*/edit', ['id' => $messageCardId]);
            }
        }

        return $resultRedirect->setPath('amgiftwrap/*/');
    }
}
