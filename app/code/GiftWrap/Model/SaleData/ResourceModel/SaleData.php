<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\ResourceModel;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\MessageCard\Resolver as CardResolver;
use Amasty\GiftWrap\Model\OptionSource\Status;
use Amasty\GiftWrap\Model\Wrapper\Resolver as WrapResolver;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SaleData extends AbstractDb implements \Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface
{
    /**
     * Tables which stores temp information (e.g. quote wraps in cart)
     *
     * @var array
     */
    private $tablesNeedValidation = [
        SaleDataResourceInterface::QUOTE_WRAP_TABLE,
        SaleDataResourceInterface::QUOTE_ADDRESS_WRAP_TABLE
    ];

    /**
     * @var CardResolver
     */
    private $cardResolver;

    /**
     * @var WrapResolver
     */
    private $wrapResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CardResolver $cardResolver,
        WrapResolver $wrapResolver,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->cardResolver = $cardResolver;
        $this->wrapResolver = $wrapResolver;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    protected function _construct()
    {
        return $this;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param DataObject $entity
     * @return DataObject
     */
    public function loadData($tableName, $fieldName, $entity)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable($tableName))
            ->where($fieldName . ' = ?', $entity->getId())
            ->limit(1);
        $wrapData = $this->getConnection()->fetchRow($select);
        if (!empty($wrapData)) {
            foreach ($wrapData as $name => $value) {
                $entity->setData('am_gift_wrap_' . $name, $value)
                    ->setOrigData('am_gift_wrap_' . $name, $value);
            }
        }

        return $entity;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param DataObject $entity
     * @return $this
     */
    public function saveData($tableName, $fieldName, $entity)
    {
        if ($entity->isDeleted() && $entity->getId()) {
            $this->getConnection()->delete(
                $this->getTable($tableName),
                $fieldName . ' = ' . $entity->getId()
            );
        } else {
            $insertData = [
                $fieldName => $entity->getId(),
            ];
            foreach ($entity->getData() as $key => $value) {
                if (strpos($key, 'am_gift_wrap_') === 0) {
                    $insertData[str_replace('am_gift_wrap_', '', $key)] = $value;
                }
            }
            $this->getConnection()->insertOnDuplicate($this->getTable($tableName), $insertData);
        }

        return $this;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    public function loadCollectionData($tableName, $fieldName, AbstractCollection $collection)
    {
        if ($collection->isLoaded()) {
            $collectionItems = $collection->getItems();
            if (count($collectionItems)) {
                $allIds = array_keys($collectionItems);
                $select = $this->getConnection()
                    ->select()
                    ->from($this->getTable($tableName))
                    ->where($fieldName . ' IN(?)', $allIds);

                $wrapData = $this->getConnection()->fetchAll($select);
                foreach ($wrapData as $data) {
                    $item = $collectionItems[$data[$fieldName]];
                    foreach ($data as $name => $value) {
                        $item->setData('am_gift_wrap_' . $name, $value)
                            ->setOrigData('am_gift_wrap_' . $name, $value);
                    }
                }
            }
        }
        return $collection;
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param Quote|Quote\Address|mixed $entity
     * @return mixed
     */
    public function loadItemsData($tableName, $fieldName, $entity)
    {
        if ($entity instanceof AbstractCollection) {
            return $this->loadCollectionData($tableName, $fieldName, $entity);
        }

        $items = [];
        $select = $this->getConnection()
            ->select()
            ->from(['main_table' => $this->getTable($tableName)])
            ->where($fieldName . ' = ?', $entity->getId());

        $wrapItems = $this->getConnection()->fetchAll($select);

        $needStatusValidation = in_array($tableName, $this->tablesNeedValidation);
        if ($needStatusValidation && $wrapItems) {
            $storeId = $this->getStoreId($entity);
            $wraps = $this->wrapResolver->getAssoc($storeId, array_column($wrapItems, WrapInterface::WRAP_ID));
            $cards = $this->cardResolver->getAssoc($storeId, array_column($wrapItems, WrapInterface::CARD_ID));
        }

        foreach ($wrapItems as $wrapItem) {
            $item = new DataObject();
            foreach ($wrapItem as $name => $value) {
                $prefix = 'am_gift_wrap_';
                $item->setData($prefix . $name, $value)
                    ->setOrigData($prefix . $name, $value);
            }
            $items[$item->getAmGiftWrapEntityId()] = $item;

            if ($needStatusValidation) {
                if (!isset($wraps[$item->getAmGiftWrapWrapId()])
                    || $wraps[$item->getAmGiftWrapWrapId()]->getStatus() != Status::ENABLED
                ) {
                    $item->setIsDeleted(true);
                    $recollectQuote = true;
                } elseif ($item->getAmGiftWrapCardId()
                    && (!isset($cards[$item->getAmGiftWrapCardId()])
                        || $cards[$item->getAmGiftWrapCardId()]->getStatus() != Status::ENABLED)
                ) {
                    $this->clearCardData($item);
                    $recollectQuote = true;
                }
            }
        }

        $entity->setWrapItems($items);

        if (isset($recollectQuote)) {
            $this->recollectQuote($entity);
        }

        return $entity;
    }

    /**
     * Recollect totals after some changes with quote items (e.g. disabling wrap or card from admin)
     *
     * @param mixed $entity
     * @throws \Exception
     */
    private function recollectQuote($entity)
    {
        $entity->setIsAmNeedReloadItems(true);
        if ($entity instanceof Quote) {
            $entity->collectTotals()->save();
        }
    }

    /**
     * @param Quote|Quote\Address $entity
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreId($entity)
    {
        $storeId = $entity->getQuote() ? $entity->getQuote()->getStoreId() : $entity->getStoreId();
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $storeId;
    }

    /**
     * @param DataObject $card
     */
    private function clearCardData(DataObject $card)
    {
        $card->setAmGiftWrapCardId(null);
        $card->setAmGiftWrapCardPrice(null);
        $card->setAmGiftWrapBaseCardPrice(null);
    }

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param mixed $entity
     * @return mixed
     */
    public function saveItemsData($tableName, $fieldName, $entity)
    {
        $salesItems = [$entity];
        if ($entity instanceof AbstractCollection && $entity->isLoaded()) {
            $salesItems = $entity->getItems();
        }

        foreach ($salesItems as $salesItem) {
            if ($salesItem->isDeleted() && $salesItem->getId()) {
                $this->getConnection()->delete(
                    $this->getTable($tableName),
                    $fieldName . ' = ' . $salesItem->getId()
                );
            } elseif ($entity->getIsWrapSaveDisabled()) {
                continue;
            } else {
                $wrapItems = $salesItem->getWrapItems() ?? [];

                // check for available items for wraps
                if ($salesItem->getQty()) {
                    $this->syncWrappedQty($salesItem);
                }

                foreach ($wrapItems as $item) {
                    if ($item->getIsDeleted()) {
                        if ($item->getAmGiftWrapEntityId()) {
                            $this->getConnection()->delete(
                                $this->getTable($tableName),
                                'entity_id = ' . $item->getAmGiftWrapEntityId()
                            );
                        }
                        continue;
                    }
                    $insertData = [];
                    foreach ($item->getData() as $key => $value) {
                        if (strpos($key, 'am_gift_wrap_') === 0) {
                            $insertData[str_replace('am_gift_wrap_', '', $key)] = $value;
                        }
                    }
                    $insertData[$fieldName] = $salesItem->getId();
                    try {
                        $this->getConnection()->insertOnDuplicate($this->getTable($tableName), $insertData);
                    } catch (\Exception $exception) {
                        $this->logger->error($exception->getMessage());
                        continue;// item was removed. TODO check this case CAT-7879
                    }
                }
            }
        }

        return $entity;
    }

    /**
     * @param $quoteItem
     */
    private function syncWrappedQty($quoteItem)
    {
        if ($wrapItems = $quoteItem->getWrapItems()) {
            $itemQty = $quoteItem->getQty();
            $wrappedQty = 0;
            $modifiedKey = null;
            $modifiedItem = null;
            foreach ($wrapItems as $key => $item) {
                $wrappedQty += (float)$item->getAmGiftWrapWrapQty();
                if ($item->getIsModified()) {
                    $modifiedKey = $key;
                    $modifiedItem = $item;
                }
            }

            if ($modifiedKey !== null) {
                unset($wrapItems[$modifiedKey]);
                $wrapItems[] = $modifiedItem;
            }

            if ($wrappedQty > $itemQty) {
                $needUnwrapQty = $wrappedQty - $itemQty;
                // if wrapped qty overfill items qty , remove excess wrapped qty from last modified wraps
                $wrapItems = array_reverse($wrapItems);
                foreach ($wrapItems as $item) {
                    if ((float)$item->getAmGiftWrapWrapQty() > $needUnwrapQty) {
                        $item->setAmGiftWrapWrapQty((float)$item->getAmGiftWrapWrapQty() - $needUnwrapQty);
                        break;
                    } else {
                        $item->setIsDeleted(true);
                        $needUnwrapQty = $needUnwrapQty - (float)$item->getAmGiftWrapWrapQty();
                    }
                }
            }
        }
    }

    /**
     * @param QuoteItem $quoteItem
     * @return bool
     */
    public function isItemHasFreeQty(QuoteItem $quoteItem)
    {
        $wrappedQty = 0;
        if ($wrapItems = $quoteItem->getWrapItems()) {
            foreach ($wrapItems as $key => $item) {
                if (!$item->getIsDeleted()) {
                    $wrappedQty += (float)$item->getAmGiftWrapWrapQty();
                }
            }
        }

        return $quoteItem->getQty() > $wrappedQty;
    }

    /**
     * @param QuoteItem $quoteItem
     * @return float
     */
    public function getQtyForWrap(QuoteItem $quoteItem)
    {
        $wrappedQty = 0;
        if ($wrapItems = $quoteItem->getWrapItems()) {
            foreach ($wrapItems as $key => $item) {
                $wrappedQty += (float) $item->getAmGiftWrapWrapQty();
            }
        }

        return $quoteItem->getQty() - $wrappedQty;
    }

    /**
     * Fetch wrap_id and card_id for choosen quote_id from table amasty_giftwrap_quote_item
     * @param $quoteId
     * @return array
     */
    public function loadWrapsByQuoteId($quoteId)
    {
        $select = $this->getConnection()->select()->from(
            ['quote_wrap' => $this->getTable(SaleDataResourceInterface::QUOTE_WRAP_TABLE)],
            [
                WrapInterface::ID,
                WrapInterface::WRAP_ID,
                WrapInterface::CARD_ID,
                WrapInterface::GIFT_MESSAGE,
                WrapInterface::BASE_PRICE,
                WrapInterface::BASE_CARD_PRICE,
                WrapInterface::IS_RECEIPT_HIDDEN,
                WrapInterface::WRAP_NAME
            ]
        )->join(
            ['quote_item' => $this->getTable(SaleDataResourceInterface::QUOTE_ITEM_TABLE)],
            'quote_item.quote_wrap_id = quote_wrap.entity_id',
            [
                'quote_item_ids' => 'GROUP_CONCAT(quote_item_id SEPARATOR ",")',
                'wrap_qty' => 'GROUP_CONCAT(wrap_qty SEPARATOR ",")'
            ]
        )->group(
            'quote_wrap.entity_id'
        )->where('quote_wrap.quote_id = ?', $quoteId);

        $rows = $this->getConnection()->fetchAssoc($select);
        array_walk($rows, [$this, 'convertQty']);

        return $rows;
    }

    /**
     * @param $item
     * @param $key
     */
    public function convertQty(&$item, $key)
    {
        $ids = explode(',', $item['quote_item_ids']);
        $qty = explode(',', $item['wrap_qty']);
        $item['quote_item_ids'] = array_combine($ids, $qty);
        unset($item['wrap_qty']);
    }

    /**
     * Fetch info about wrapped items (item_id, wrap_qty, etc.) by quote_wrap_id
     * @param array $wrapIds
     * @return array
     */
    public function loadItemsDataByWrapIds($wrapIds = [])
    {
        $select = $this->getConnection()->select()
            ->from(
                ['quote_item' => $this->getTable(SaleDataResourceInterface::QUOTE_ITEM_TABLE)],
                ['quote_wrap_id', 'item_id' => 'quote_item_id', 'qty' => 'wrap_qty']
            )->where('quote_wrap_id IN (?)', $wrapIds);
        $data = $this->getConnection()->fetchAll($select);
        $itemsInfo = [];
        foreach ($data as $datum) {
            $itemsInfo[$datum['quote_wrap_id']][] = [
                'item_id' => $datum['item_id'],
                'qty' => $datum['qty']
            ];
        }

        return $itemsInfo;
    }

    /**
     * Fetch wrap_id and card_id for choosen order_id from table amasty_giftwrap_order_item
     * @param $orderId
     * @param array $additionalFields
     * @return array
     */
    public function loadWrapsByOrderId($orderId, $additionalFields = [])
    {
        $select = $this->getConnection()->select()->from(
            ['order_wrap' => $this->getTable(SaleDataResourceInterface::ORDER_WRAP_TABLE)],
            array_merge([
                WrapInterface::ID,
                WrapInterface::WRAP_ID,
                WrapInterface::CARD_ID,
                WrapInterface::GIFT_MESSAGE,
                WrapInterface::BASE_PRICE,
                WrapInterface::BASE_CARD_PRICE,
                WrapInterface::WRAP_NAME,
                WrapInterface::CARD_NAME
            ], $additionalFields)
        )->join(
            ['order_item' => $this->getTable(SaleDataResourceInterface::ORDER_ITEM_TABLE)],
            'order_item.quote_wrap_id = order_wrap.entity_id',
            []
        )->where('order_wrap.order_id = ?', $orderId);

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * Fetch info about wrapped items (item_id, wrap_qty, etc.) by order_wrap_id
     * @param array $wrapIds
     * @return array
     */
    public function loadOrderItemsDataByWrapIds($wrapIds = [])
    {
        $select = $this->getConnection()->select()
            ->from(
                ['order_item' => $this->getTable(SaleDataResourceInterface::ORDER_ITEM_TABLE)],
                ['quote_wrap_id', 'item_id' => 'order_item_id', 'qty' => 'wrap_qty']
            )->where('quote_wrap_id IN (?)', $wrapIds);
        $data = $this->getConnection()->fetchAll($select);
        $itemsInfo = [];
        foreach ($data as $datum) {
            $itemsInfo[$datum['quote_wrap_id']][] = [
                'item_id' => $datum['item_id'],
                'qty' => $datum['qty']
            ];
        }

        return $itemsInfo;
    }
    public function wrapImage($wrapId)
    {
        $select = "SELECT image,name FROM amasty_giftwrap_wrap_store WHERE wrap_id = '".$wrapId."'";
        $data = $this->getConnection()->fetchAll($select);
        return $data;        
    }  
    /**
     * Fetch Wrap Image from wrap_id
     * @param array $wrapId
     * @return array
     */    
    public function cardImage($cardId)
    {
        $select = "SELECT image,name FROM amasty_giftwrap_message_card_store WHERE message_card_id = '".$cardId."'";
        $data = $this->getConnection()->fetchAll($select);
        return $data;        
    }  
}
