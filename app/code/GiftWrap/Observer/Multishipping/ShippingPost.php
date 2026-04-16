<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer\Multishipping;

use Amasty\GiftWrap\Model\SaleData\WrapManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item;
use Psr\Log\LoggerInterface;

class ShippingPost implements ObserverInterface
{
    /**
     * @var WrapManagement
     */
    private $wrapManagement;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        WrapManagement $wrapManagement,
        ManagerInterface $messageManager,
        LoggerInterface $logger
    ) {
        $this->wrapManagement = $wrapManagement->setType(WrapManagement::ADDRESS_TYPE);
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getRequest();
        /** @var Quote $quote */
        $quote = $observer->getQuote();
        if ($allWrapData = $request->getParam('amwrap')) {
            foreach ($quote->getAllAddresses() as $address) {
                if (isset($allWrapData[$address->getId()]) && ($wrapData = $allWrapData[$address->getId()])) {
                    if ($address->getAddressType() === Address::TYPE_SHIPPING) {
                        $this->wrapAddress($address, $wrapData);
                    }
                }
            }
        }
    }

    /**
     * @param Address $address
     * @param array $wrapData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function wrapAddress(Address $address, array $wrapData)
    {
        /** @var Item $addressItem */
        foreach ($address->getItemsCollection() as $addressItem) {
            if (isset($wrapData[WrapManagement::FINISH_KEY])) {
                $quoteWrap = $this->wrapManagement->wrapQuoteItem(
                    $addressItem,
                    null,
                    $wrapData
                );
                $wrapData['existing_wrap_id'] = $quoteWrap->getId();
            } elseif (isset($wrapData['existing_wrap_id'])) {
                $this->wrapManagement->markIsDeleted($address, $wrapData['existing_wrap_id']);
            }
        }
    }
}
