<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData;

use Amasty\GiftWrap\Api\MessageCardRepositoryInterface;
use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Api\SaleData\WrapRepositoryInterface as SaleWrapRepository;
use Amasty\GiftWrap\Api\WrapRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem as AbstractItem;
use Magento\Store\Model\StoreManagerInterface;

class WrapManagement
{
    public const QUOTE_TYPE = 0;
    public const ADDRESS_TYPE = 1;

    public const FINISH_KEY = 'finish';

    /**
     * @var SaleWrapRepository
     */
    private $quoteWrapRepository;

    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var MessageCardRepositoryInterface
     */
    private $cardRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SaleWrapRepository
     */
    private $addressWrapRepository;

    /**
     * @var string
     */
    private $type;

    public function __construct(
        SaleWrapRepository $quoteWrapRepository,
        SaleWrapRepository $addressWrapRepository,
        WrapRepositoryInterface $wrapRepository,
        MessageCardRepositoryInterface $cardRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteWrapRepository = $quoteWrapRepository;
        $this->wrapRepository = $wrapRepository;
        $this->cardRepository = $cardRepository;
        $this->storeManager = $storeManager;
        $this->addressWrapRepository = $addressWrapRepository;
    }

    /**
     * @param AbstractItem $quoteItem
     * @param float|null $qtyToWrap
     * @param array $wrapData
     * @return WrapInterface|AbstractWrap|void
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function wrapQuoteItem(AbstractItem $quoteItem, $qtyToWrap, array $wrapData)
    {
        if ($repository = $this->getSaleRepository()) {
            /** @var AbstractWrap $quoteWrap */
            if (!empty($wrapData['existing_wrap_id'])) {
                $quoteWrap = $repository->getById($wrapData['existing_wrap_id']);
            } else {
                $quoteWrap = $repository->getNewItem();
            }
            $m_qty = $qtyToWrap;
            if ($m_qty === null) {
                $wrap_qty = 0;
                if($i_qty = $quoteItem->getQty()){
                    $wrapItems = $quoteItem->getWrapItems() ?: [];
                
                    foreach ($wrapItems as $wrapItem) {           
                        $wrap_qty +=$wrapItem->getAmGiftWrapWrapQty();               
                    }
                    $m_qty = $i_qty - $wrap_qty;
                }
                
            }

            if (isset($wrapData[WrapInterface::WRAP_ID])) {
                $wrap = $this->wrapRepository->getById(
                    $wrapData[WrapInterface::WRAP_ID],
                    $quoteItem->getQuote()->getStore()->getId()
                );

                $quoteWrap->setWrapId($wrap->getEntityId());
                $quoteWrap->setBasePrice($wrap->getPrice()* (int) $m_qty);
                $quoteWrap->setWrapName($wrap->getName());

                if (isset($wrapData[WrapInterface::CARD_ID])) {
                    $card = $this->cardRepository->getById(
                        $wrapData[WrapInterface::CARD_ID],
                        $quoteItem->getQuote()->getStore()->getId()
                    );
                    $quoteWrap->setCardId($card->getEntityId());
                    $quoteWrap->setBaseCardPrice($card->getPrice() * (int) $m_qty);
                } else {
                    $quoteWrap->setCardId(null);
                    $quoteWrap->setCardPrice(null);
                    $quoteWrap->setBaseCardPrice(null);
                }

                if (isset($wrapData[WrapInterface::IS_RECEIPT_HIDDEN])) {
                    $quoteWrap->setIsReceiptHidden($wrapData[WrapInterface::IS_RECEIPT_HIDDEN]);
                } else {
                    $quoteWrap->setIsReceiptHidden(0);
                }

                if (isset($wrapData[WrapInterface::GIFT_MESSAGE])) {
                    $quoteWrap->setGiftMessage($wrapData[WrapInterface::GIFT_MESSAGE]);
                } else {
                    $quoteWrap->setGiftMessage(null);
                }

                $repository->save($quoteWrap);

                $quoteWrap->addForItem($quoteItem, $qtyToWrap);

                $quoteEntity = $this->getQuoteEntity($quoteItem);
                $wrapItems = $quoteEntity->getWrapItems() ?: [];
                $wrapItems[$quoteWrap->getId()] = $quoteWrap;
                $quoteEntity->setWrapItems($wrapItems);
            }

            return $quoteWrap;
        } else {
            $this->throwInputType();
        }
    }

    /**
     * @param int $quoteWrapId
     * @throws CouldNotDeleteException
     * @throws \Exception
     */
    public function removeQuoteWrap($quoteWrapId)
    {
        if ($repository = $this->getSaleRepository()) {
            $repository->deleteById($quoteWrapId);
        } else {
            $this->throwInputType();
        }
    }

    /**
     * @param array $wrapData
     * @return WrapInterface|AbstractWrap|void
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function updateWrap($wrapData)
    {
        if ($repository = $this->getSaleRepository()) {
            /** @var AbstractWrap $quoteWrap */
            if (!empty($wrapData['existing_wrap_id'])) {
                $quoteWrap = $repository->getById($wrapData['existing_wrap_id']);

                if (isset($wrapData[WrapInterface::WRAP_ID])) {
                    $wrap = $this->wrapRepository->getById(
                        $wrapData[WrapInterface::WRAP_ID],
                        $this->storeManager->getStore()->getId()
                    );

                    $quoteWrap->setWrapId($wrap->getEntityId());
                    $quoteWrap->setBasePrice($wrap->getPrice() * (int) $qtyToWrap);
                    $quoteWrap->setWrapName($wrap->getName());

                    if (isset($wrapData[WrapInterface::CARD_ID])) {
                        $card = $this->cardRepository->getById(
                            $wrapData[WrapInterface::CARD_ID],
                            $this->storeManager->getStore()->getId()
                        );
                        $quoteWrap->setCardId($card->getEntityId());
                        $quoteWrap->setBaseCardPrice($card->getPrice() * (int) $qtyToWrap);
                    } else {
                        $quoteWrap->setCardId(null);
                        $quoteWrap->setBaseCardPrice(0);
                        $quoteWrap->setCardPrice(0);
                    }

                    if (isset($wrapData[WrapInterface::IS_RECEIPT_HIDDEN])) {
                        $quoteWrap->setIsReceiptHidden($wrapData[WrapInterface::IS_RECEIPT_HIDDEN]);
                    } else {
                        $quoteWrap->setIsReceiptHidden(0);
                    }

                    if (isset($wrapData[WrapInterface::GIFT_MESSAGE])) {
                        $quoteWrap->setGiftMessage($wrapData[WrapInterface::GIFT_MESSAGE]);
                    }

                    $repository->save($quoteWrap);
                }

                return $quoteWrap;
            }
        } else {
            $this->throwInputType();
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return WrapManagement
     */
    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @throws LocalizedException
     */
    private function throwInputType()
    {
        throw new LocalizedException(__('Need input repository type'));
    }

    /**
     * @return SaleWrapRepository|null
     */
    private function getSaleRepository()
    {
        $repository = null;
        switch ($this->getType()) {
            case self::QUOTE_TYPE:
                $repository = $this->quoteWrapRepository;
                break;
            case self::ADDRESS_TYPE:
                $repository = $this->addressWrapRepository;
                break;
        }

        return $repository;
    }

    /**
     * @param AbstractItem $quoteItem
     * @return CartInterface|AddressInterface
     */
    private function getQuoteEntity($quoteItem)
    {
        $quoteEntity = null;
        if ($quoteItem->getQuote()->getIsMultiShipping()
            && $quoteItem->getAddress()->getItemsCollection()->getSize()) {
            $quoteEntity = $quoteItem->getAddress();
        } else {
            $quoteEntity = $quoteItem->getQuote();
        }

        return $quoteEntity;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function isProductCanWrapped(Product $product)
    {
        // wrap works only for giftcard with type TYPE_PHYSICAL
        if (($product->getTypeId() === 'giftcard' && $product->getGiftcardType() !== '1')
            || ($product->getTypeId() === 'amgiftcard' && $product->getAmGiftcardType() === '1')
        ) {
            return false;
        }
        return $product->getAmAvailableForWrapping() && $product->isSaleable();
    }

    /**
     * @param AddressInterface|Quote $entity
     * @param int $quoteWrapId
     * @return bool
     */
    public function markIsDeleted($entity, $quoteWrapId)
    {
        $result = false;
        $wrapItems = $entity->getWrapItems() ?: [];
        foreach ($wrapItems as $wrapItem) {
            if ($wrapItem->getAmGiftWrapEntityId() == $quoteWrapId) {
                $result = true;
                $wrapItem->setIsDeleted(true);
                break;
            }
        }

        return $result;
    }

    /**
     * @param AbstractItem $quoteItem
     * @param int $quoteWrapId
     * @param float $wrapQty
     */
    public function updateQuoteItem(AbstractItem $quoteItem, int $quoteWrapId, float $wrapQty)
    {
        $wrapItems = $quoteItem->getWrapItems() ?: [];
        foreach ($wrapItems as $wrapItem) {
            if ($wrapItem->getAmGiftWrapQuoteWrapId() == $quoteWrapId) {
                $wrapItem->setAmGiftWrapWrapQty($wrapQty);
                break;
            }
        }
    }

    /**
     * @param AbstractItem $quoteItem
     * @param int $quoteWrapId
     */
    public function removeQuoteItem(AbstractItem $quoteItem, int $quoteWrapId)
    {
        $wrapItems = $quoteItem->getWrapItems() ?: [];
        foreach ($wrapItems as $wrapItem) {
            if ($wrapItem->getAmGiftWrapQuoteWrapId() == $quoteWrapId) {
                $wrapItem->setIsDeleted(true);
                break;
            }
        }
    }
}
