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
use Amasty\GiftWrap\Api\MessageCardRepositoryInterface;
use Amasty\GiftWrap\Api\WrapRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

class CartUpdateObserver implements ObserverInterface
{
    
    private $wrapManagement;
    
    private $wrapResource;
    
    private $wrapRepository;

    private $cardRepository;
    
    private $messageManager;

    public function __construct(
        WrapManagement $wrapManagement, 
        Wrap $wrapResource,
        WrapRepositoryInterface $wrapRepository,
        MessageCardRepositoryInterface $cardRepository,
        ManagerInterface $messageManager    
     
    ) {
        $this->wrapManagement = $wrapManagement->setType(WrapManagement::QUOTE_TYPE);       
        $this->wrapResource = $wrapResource;  
        $this->wrapRepository = $wrapRepository;
        $this->cardRepository = $cardRepository;
        $this->messageManager = $messageManager;
        
    }
    
    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {         
        $cart = $observer->getEvent()->getCart();
        $updatedItems = $cart->getQuote()->getAllItems(); // all items in cart        
        
      /*  $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quote = $objectManager->get(\Magento\Checkout\Model\Cart::class)->getQuote(); */
        
         
         
        foreach ($updatedItems as $quoteItem) {
            $id = 0;
            $qty = 1;
            if ($quoteItem) {
                try {       
                    foreach ($quoteItem->getWrapItems() as $wrapItem) { 
                        $id = $wrapItem->getAmGiftWrapQuoteWrapId();
                        $modelWrap = $this->wrapResource->load($id);
                        $wrap_id = $modelWrap->getWrapId();
                        $wrap = $this->wrapRepository->getById(
                                $wrap_id,
                                $quoteItem->getQuote()->getStore()->getId()
                        );                        
                        $card_id = $modelWrap->getCardId();
                        $card = $this->cardRepository->getById(
                            $card_id,
                            $quoteItem->getQuote()->getStore()->getId()
                        );
                        $qty = (int) $wrapItem->getAmGiftWrapWrapQty();
                        
                        
                        
                        $modelWrap->setPrice($wrap->getPrice() * $qty);
                        $modelWrap->setBasePrice($wrap->getPrice() * $qty);
                        $modelWrap->setPriceInclTax($wrap->getPrice() * $qty);
                        $modelWrap->setBasePriceInclTax($wrap->getPrice() *  $qty);
                        
                        $modelWrap->setCardPrice($card->getPrice() *  $qty);
                        $modelWrap->setBaseCardPrice($card->getPrice() *  $qty);
                        $modelWrap->setCardPriceInclTax($card->getPrice() *  $qty);
                        $modelWrap->setBaseCardPriceInclTax($card->getPrice() *  $qty);
                        $modelWrap->save();
                        
                       
                    }                   
                 
                } catch (NoSuchEntityException $exception) {
                    $this->messageManager->addErrorMessage(__(
                        'Can\'t update wrap'
                    ));
                    //$this->logger->error($exception->getMessage());
                } 
            }    
        }
          
    }    
   
}
