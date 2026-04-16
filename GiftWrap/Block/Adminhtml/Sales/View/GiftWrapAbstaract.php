<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Adminhtml\Sales\View;

use Amasty\GiftWrap\Api\MessageCardRepositoryInterface;
use Amasty\GiftWrap\Api\WrapRepositoryInterface;
use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\MessageCard\Resolver as CardResolver;
use Amasty\GiftWrap\Model\PriceConverter;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Model\Wrapper\Resolver as WrapResolver;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

abstract class GiftWrapAbstaract extends Template
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $existingWraps = null;

    /**
     * @var WrapResolver
     */
    protected $wrapResolver;

    /**
     * @var CardResolver
     */
    protected $cardResolver;

    /**
     * @var array|null
     */
    protected $wrapData = null;

    /**
     * @var array|null
     */
    protected $wraps = null;

    /**
     * @var array|null
     */
    protected $cards = null;

    /**
     * @var array|null
     */
    protected $itemsData = null;

    /**
     * @var SaleData
     */
    protected $saleData;

    /**
     * @var PriceConverter
     */
    protected $priceConverter;

    /**
     * @var WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var MessageCardRepositoryInterface
     */
    private $messageCardRepository;

    public function __construct(
        Registry $registry,
        SaleData $saleData,
        WrapResolver $wrapResolver,
        CardResolver $cardResolver,
        PriceConverter $priceConverter,
        Template\Context $context,
        WrapRepositoryInterface $wrapRepository,
        MessageCardRepositoryInterface $messageCardRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->wrapResolver = $wrapResolver;
        $this->saleData = $saleData;
        $this->cardResolver = $cardResolver;
        $this->priceConverter = $priceConverter;
        $this->wrapRepository = $wrapRepository;
        $this->messageCardRepository = $messageCardRepository;
    }

    /**
     * @return SaleData
     */
    public function getSaleDataModel()
    {
        return $this->saleData;
    }

    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
        return $this;
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
                $total = $this->convertPrice($wrapItem['base_price'] + $wrapItem['base_card_price']);
                $items = $this->getItemsByWrapOrderId($wrapItem['entity_id']);
                $isReceiptHidden = isset($wrapItem[WrapInterface::IS_RECEIPT_HIDDEN])
                    && $wrapItem[WrapInterface::IS_RECEIPT_HIDDEN];
                try {
                    $wrap = $this->wrapRepository->getById($wrapItem['wrap_id']);
                } catch (NoSuchEntityException $entityException) {
                    $wrap = null;
                }
                try {
                    $card = $this->messageCardRepository->getById($wrapItem['card_id']);
                } catch (NoSuchEntityException $entityException) {
                    $card = null;
                }
                $this->wrapData[] = new DataObject([
                    'wrap' => $wrapName,
                    'wrap_id' => $wrap ? $wrap->getId() : null,
                    'card' => $cardName,
                    'card_id' => $card ? $card->getId() : null,
                    'gift_message' => $wrapItem['gift_message'] ?? '',
                    'hide_price' => $isReceiptHidden ? __('Yes') : __('No'),
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
                    $item = clone $this->getOrder()->getItemById($data['item_id']);
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
    protected function getExistingWraps()
    {
        if ($this->existingWraps === null) {
            $this->existingWraps = $this->getSaleDataModel()->loadWrapsByOrderId(
                $this->getOrder()->getId(),
                [WrapInterface::IS_RECEIPT_HIDDEN]
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
            'hide_price' => __('Hidden Prices on Receipt'),
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
            case 'wrap':
                if ($wrapId = $this->getColumnHtml($item, 'wrap_id')) {
                    $value = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $this->getWrapUrl($wrapId),
                        $value
                    );
                }
                break;
            case 'card':
                if ($cardId = $this->getColumnHtml($item, 'card_id')) {
                    $value = sprintf('<a href="%s" target="_blank">%s</a>', $this->getCardUrl($cardId), $value);
                }
                break;
            case 'gift_message':
                $value = nl2br($value);
                break;
        }

        return $value;
    }

    /**
     * @param int $id
     * @return string
     */
    public function getWrapUrl($id)
    {
        return $this->getUrl('amgiftwrap/wrap/edit', ['id' => $id]);
    }

    /**
     * @param int $id
     * @return string
     */
    public function getCardUrl($id)
    {
        return $this->getUrl('amgiftwrap/message_card/edit', ['id' => $id]);
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->getOrder()) {
            return '';
        }

        return parent::toHtml();
    }
}
