<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Sales\Order\View;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\MessageCard\Resolver as CardResolver;
use Amasty\GiftWrap\Model\PriceConverter;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Model\Wrapper\Resolver as WrapResolver;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;

class Info extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $existingWraps = null;

    /**
     * @var WrapResolver
     */
    private $wrapResolver;

    /**
     * @var CardResolver
     */
    private $cardResolver;

    /**
     * @var array|null
     */
    private $wrapData = null;

    /**
     * @var array|null
     */
    private $wraps = null;

    /**
     * @var array|null
     */
    private $cards = null;

    /**
     * @var array|null
     */
    private $itemsData = null;

    /**
     * @var SaleData
     */
    private $saleData;

    /**
     * @var PriceConverter
     */
    private $priceConverter;

    /**
     * @var bool
     */
    private $forEmail = false;

    public function __construct(
        Registry $registry,
        SaleData $saleData,
        WrapResolver $wrapResolver,
        CardResolver $cardResolver,
        PriceConverter $priceConverter,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->wrapResolver = $wrapResolver;
        $this->saleData = $saleData;
        $this->cardResolver = $cardResolver;
        $this->priceConverter = $priceConverter;
    }

    /**
     * @return SaleData
     */
    public function getSaleDataModel()
    {
        return $this->saleData;
    }

    public function getOrder(): Order
    {
        return $this->registry->registry('current_order') ?? $this->_getData('order');
    }

    public function isForEmail(): bool
    {
        return $this->forEmail;
    }

    public function setForEmail(bool $forEmail): void
    {
        $this->forEmail = $forEmail;
    }

    /**
     * @return array|null
     */
    public function getWrapData()
    {
        if ($this->wrapData === null) {
            $this->wrapData = [];
            foreach ($this->getExistingWraps() as $wrapItem) {
                $wrapName = $wrapItem[WrapInterface::WRAP_NAME];
                $cardName = $wrapItem[WrapInterface::CARD_NAME] ?: __('No');
                $total = $this->convertPrice(
                    $wrapItem['base_price'] + $wrapItem['base_card_price'],
                    $this->getOrder()->getStoreId()
                );
                $items = $this->getItemsByWrapOrderId($wrapItem['entity_id']);

                $this->wrapData[] = new DataObject([
                    'wrap' => $wrapName,
                    'card' => $cardName,
                    'gift_message' => $wrapItem['gift_message'] ?? '',
                    'total' => $total,
                    'item_collection' => $items
                ]);

            }
        }

        return $this->wrapData;
    }

    /**
     * @param $orderWrapId
     * @return array|mixed
     */
    public function getItemsByWrapOrderId($orderWrapId)
    {
        if ($this->itemsData === null) {
            $this->itemsData = [];
            $allWrapItems = $this->getSaleDataModel()
                ->loadOrderItemsDataByWrapIds(array_keys($this->getExistingWraps()));
            foreach ($allWrapItems as $quoteWrapItemId => $itemData) {
                foreach ($itemData as $data) {
                    $item = $this->getOrderItemById((int) $data['item_id']);
                    if ($item) {
                        $item->setRequestedQty($data['qty'] * 1);
                        $this->itemsData[$quoteWrapItemId][] = $item;
                    }
                }
            }
        }

        return $this->itemsData[$orderWrapId] ?? [];
    }

    /**
     * @param $wrapId
     * @return mixed
     */
    public function getWrapById($wrapId)
    {
        if ($this->wraps === null) {
            $this->wraps = $this->wrapResolver->getAssoc(
                $this->getOrder()->getStoreId(),
                array_column($this->getExistingWraps(), 'wrap_id')
            );
        }

        return $this->wraps[$wrapId] ?? null;
    }

    /**
     * @param $cardId
     * @return mixed
     */
    public function getCardById($cardId)
    {
        if ($this->cards === null) {
            $this->cards = $this->cardResolver->getAssoc(
                $this->getOrder()->getStoreId(),
                array_column($this->getExistingWraps(), 'card_id')
            );
        }

        return $this->cards[$cardId] ?? null;
    }

    /**
     * @return array
     */
    private function getExistingWraps()
    {
        if ($this->existingWraps === null) {
            $this->existingWraps = $this->getSaleDataModel()->loadWrapsByOrderId(
                $this->getOrder()->getId()
            );
        }

        return $this->existingWraps;
    }

    /**
     * @param $price
     * @return float|string
     */
    public function convertPrice($price)
    {
        return $this->priceConverter->convertPrice($price, null, $this->getOrder()->getOrderCurrency());
    }

    /**
     * @return bool
     */
    public function isAllowedToDisplay()
    {
        return (bool)$this->getWrapData();
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            'wrap' => __('Gift Wrap'),
            'card' => __('Gift Message Card'),
            'gift_message' => __('Card Message'),
            'total' => __('Total')
        ];
    }

    /**
     * @param mixed $item
     * @param string $columnName
     * @return string
     */
    public function getColumnHtml($item, $columnName)
    {
        if (is_array($item)) {
            $value = $item[$columnName] ?? '';
        } else {
            $value = $item->getData($columnName);
        }
        $value = $this->escapeHtml($value);
        switch ($columnName) {
            case 'gift_message':
                $value = nl2br($value);
                break;
        }

        return $value;
    }

    private function getOrderItemById(int $orderItemId): ?OrderItemInterface
    {
        foreach ($this->getOrder()->getItems() as $orderItem) {
            if ($orderItemId === (int) $orderItem->getId()) {
                return $orderItem;
            }
        }

        return null;
    }
}
