<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftWrap
 */


namespace Amasty\GiftWrap\Observer;

use Amasty\GiftWrap\Model\SaleData\WrapManagement;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amasty\GiftWrap\Model\SaleData\Quote\Wrap;
use Psr\Log\LoggerInterface;
class ProductRemoveAfter implements ObserverInterface
{
    
    private $wrapManagement;
    
    private $wrapResource;
    
    private $logger;

    public function __construct(
        WrapManagement $wrapManagement, 
        Wrap $wrapResource,
        LoggerInterface $logger    
     
    ) {
        $this->wrapManagement = $wrapManagement->setType(WrapManagement::QUOTE_TYPE);       
        $this->wrapResource = $wrapResource;
        $this->logger = $logger;
        
    }
    
    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {         
        $request = $observer->getEvent()
                            ->getControllerAction()
                            ->getRequest();

        $itemId = $request->getParam('id'); 
        if (!$itemId) {
            return;
        }
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quote = $objectManager->get(\Magento\Checkout\Model\Cart::class)->getQuote();
        $quoteItem = $quote->getItemById($itemId);
      //  $quoteItem = $observer->getQuoteItem();        
        $id = 0;
        if ($quoteItem) {
            try {       
                foreach ($quoteItem->getWrapItems() as $wrapItem) { 
                    $id = $wrapItem->getAmGiftWrapQuoteWrapId();
                    $modelWrap = $this->wrapResource->load($id);
                    $modelWrap->delete();
                 
                }                   
                 
            } catch (NoSuchEntityException $exception) {
                   /* $this->messageManager->addErrorMessage(__(
                        'Can\'t remove product wrap %1 for this wrap'
                    )); */
                    $this->logger->error($exception->getMessage());
            } 
        }                        
         
        return;  
          
    }    
   
}
