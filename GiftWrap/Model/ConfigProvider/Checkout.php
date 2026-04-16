<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\ConfigProvider;

use Amasty\GiftWrap\Model\ConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Checkout implements ConfigProviderInterface
{
    /**
     * @var ButtonDataProvider
     */
    private $buttonDataProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var QuoteWrapDataProvider
     */
    private $quoteWrapDataProvider;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ButtonDataProvider $buttonDataProvider,
        ConfigProvider $configProvider,
        QuoteWrapDataProvider $quoteWrapDataProvider,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->buttonDataProvider = $buttonDataProvider;
        $this->configProvider = $configProvider;
        $this->quoteWrapDataProvider = $quoteWrapDataProvider;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $items = $this->quoteWrapDataProvider->getWrapItemsData();
        return [
            'amGiftWrap' => [
                'giftWrappingAvailable' => $this->configProvider->isEnabledOnPage(
                    $this->buttonDataProvider->getPageType(),
                    (int) $this->storeManager->getStore()->getId()
                ),
                'giftWrappingCheckoutButtonAvailable' => $this->buttonDataProvider->isAddButtonActive(),
                'displayWrapBothPrices' => $this->configProvider->getDisplayTotalsBothPrices(),
                'displayWrapInclTaxPrice' => $this->configProvider->getDisplayTotalsInclTax(),
                'wrapItemsJson' => json_encode($items),
                'itemsCount' => count($items),
                'giftWrapCheckoutUpdateUrl' => $this->urlBuilder->getUrl('amgiftwrap/ajax_checkout/summary')
            ]
        ];
    }
}
