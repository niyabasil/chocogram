<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\ConfigProvider;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\OptionSource\Allow;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Model\Wrapper\Resolver;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use \Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManager;

class ButtonDataProvider
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var SaleData
     */
    private $saleData;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Resolver
     */
    private $wrapResolver;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Session $checkoutSession,
        SaleData $saleData,
        RequestInterface $request,
        ConfigProvider $configProvider,
        Resolver $wrapResolver,
        StoreManager $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->saleData = $saleData;
        $this->logger = $logger;
        $this->configProvider = $configProvider;
        $this->wrapResolver = $wrapResolver;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function isAddButtonActive()
    {
        try {
            $page = $this->getPageType();
            $result = $this->configProvider->isEnabledOnPage($page)
                && $this->isProductsForWrapExists()
                && $this->isActiveWrapExists();
        } catch (\Exception $e) {
            $result = false;
            $this->logger->error($e->getMessage());
        }

        return $result;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function isProductsForWrapExists()
    {
        $result = false;
        /** @var QuoteItem $quoteItem */
        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $quoteItem) {
            if ($quoteItem->getProduct()->getData(CreateProductAttributes::AVAILABLE_FOR_WRAPPING)
                && $this->saleData->isItemHasFreeQty($quoteItem)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isCheckoutPage()
    {
        $controllerName = $this->request->getControllerName();
        $routeName = $this->request->getRouteName();
        return $routeName == Allow::CHECKOUT_PAGE && $controllerName != Allow::CART_PAGE;
    }

    /**
     * @return bool
     */
    public function isCartPage()
    {
        $controllerName = $this->request->getControllerName();
        $routeName = $this->request->getRouteName();
        return $routeName == Allow::CHECKOUT_PAGE && $controllerName == Allow::CART_PAGE;
    }

    public function getPageType(): string
    {
        switch (true) {
            case $this->isCartPage():
                $page = Allow::CART_PAGE;
                break;
            case $this->isCheckoutPage():
                $page = Allow::CHECKOUT_PAGE;
                break;
            default:
                $page = Allow::PRODUCT_PAGE;
        }

        return $page;
    }

    /**
     * @throws NoSuchEntityException
     * @return bool
     */
    public function isActiveWrapExists()
    {
        $wraps = $this->wrapResolver->getAssoc($this->storeManager->getStore()->getId());
        return !empty($wraps);
    }
}
