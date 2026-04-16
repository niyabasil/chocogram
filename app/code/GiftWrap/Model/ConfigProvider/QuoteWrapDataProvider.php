<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\ConfigProvider;

use Amasty\GiftWrap\Model\MessageCard\Resolver as CardResolver;
use Amasty\GiftWrap\Model\PriceConverter;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Model\Wrapper\Resolver as WrapResolver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManager;

class QuoteWrapDataProvider
{
    public const IMAGE_WIDTH = 75;

    public const IMAGE_HEIGHT = 75;

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
    private $wrappers = null;

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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var PriceConverter
     */
    private $priceConverter;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Amasty\GiftWrap\Model\ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        CheckoutSession $checkoutSession,
        SaleData $saleData,
        WrapResolver $wrapResolver,
        CardResolver $cardResolver,
        PriceConverter $priceConverter,
        StoreManager $storeManager,
        \Amasty\GiftWrap\Model\ImageProcessor $imageProcessor,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->wrapResolver = $wrapResolver;
        $this->saleData = $saleData;
        $this->checkoutSession = $checkoutSession;
        $this->cardResolver = $cardResolver;
        $this->priceConverter = $priceConverter;
        $this->storeManager = $storeManager;
        $this->imageProcessor = $imageProcessor;
        $this->escaper = $escaper;
    }

    /**
     * @return SaleData
     */
    private function getSaleDataModel()
    {
        return $this->saleData;
    }

    /**
     * @return CheckoutSession
     */
    private function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getWrappers()
    {
        if ($this->wrappers === null) {
            $this->wrappers = $this->wrapResolver->getAssoc(
                $this->storeManager->getStore()->getId(),
                array_column($this->getExistingWraps(), 'wrap_id')
            );
        }

        return $this->wrappers;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCards()
    {
        if ($this->cards === null) {
            $this->cards = $this->cardResolver->getAssoc(
                $this->storeManager->getStore()->getId(),
                array_column($this->getExistingWraps(), 'card_id')
            );
        }

        return $this->cards;
    }

    /**
     * @return array
     */
    private function getExistingWraps()
    {
        if ($this->existingWraps === null) {
            $this->existingWraps = $this->getSaleDataModel()->loadWrapsByQuoteId(
                $this->getCheckoutSession()->getQuoteId()
            );
        }

        return $this->existingWraps;
    }

    /**
     * @param $cardId
     * @return array|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCardName($cardId)
    {
        $cards = $this->getCards();
        return isset($cards[$cardId]) ? $this->escaper->escapeHtml($cards[$cardId]->getName()) : '';
    }

    /**
     * @param $wrapId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProducts($wrapId)
    {
        if ($this->itemsData === null) {
            $this->itemsData = $this->getSaleDataModel()->loadItemsDataByWrapIds(array_keys($this->getExistingWraps()));
        }

        $itemsData = $this->itemsData[$wrapId] ?? [];

        $productsData = [];
        foreach ($itemsData as $item) {
            $name = $this->escaper->escapeHtml(
                $this->getCheckoutSession()
                    ->getQuote()
                    ->getItemsCollection()
                    ->getItemById($item['item_id'])
                    ->getName()
            );

            if (isset($item['qty'])) {
                $productsData[] = sprintf('%s <b>x%s</b>', $name, (int)$item['qty']);
            } else {
                $productsData[] = $name;
            }
        }

        return $productsData ? implode('<br/>', $productsData) : '';
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWrapItemsData()
    {
        $itemsData = [];
        $quoteWraps = $this->getWrappers();
        if (!$quoteWraps) {
            return $itemsData;
        }
        foreach ($this->existingWraps as $quoteWrapId => $quoteWrapData) {
            if (isset($quoteWraps[$quoteWrapData['wrap_id']])) {
                $quoteWrap = $quoteWraps[$quoteWrapData['wrap_id']];
                $price = ($quoteWrapData['base_price'] ?? 0) + ($quoteWrapData['base_card_price'] ?? 0);
                $itemsData[] = [
                    'name' => $this->escaper->escapeHtml($quoteWrap->getName()),
                    'image' => $this->imageProcessor->getResizedUrl(
                        $quoteWrap->getImage(),
                        self::IMAGE_WIDTH,
                        self::IMAGE_HEIGHT
                    ),
                    'card' => $this->getCardName($quoteWrapData['card_id']),
                    'price' => $this->priceConverter->convertPrice($price),
                    'message' => $this->escaper->escapeHtml($quoteWrapData['gift_message'] ?? ''),
                    'products' => $this->getProducts($quoteWrapId)
                ];
            }

        }

        return $itemsData;
    }
}
