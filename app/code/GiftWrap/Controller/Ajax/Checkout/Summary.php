<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Ajax\Checkout;

use Amasty\GiftWrap\Controller\Ajax\AbstractAction;
use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\SaleData\WrapManagement;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutInterface;
use Amasty\GiftWrap\Model\ConfigProvider\QuoteWrapDataProvider;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Summary extends AbstractAction
{
    /**
     * @var QuoteWrapDataProvider
     */
    private $quoteWrapDataProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var SaleData
     */
    private $saleData;

    public function __construct(
        Cart $cart,
        WrapManagement $wrapManagement,
        LayoutInterface $layout,
        Escaper $escaper,
        Context $context,
        QuoteWrapDataProvider $quoteWrapDataProvider,
        ConfigProvider $configProvider,
        Session $checkoutSession,
        SaleData $saleData
    ) {
        parent::__construct($cart, $wrapManagement, $layout, $escaper, $context);
        $this->quoteWrapDataProvider = $quoteWrapDataProvider;
        $this->configProvider = $configProvider;
        $this->checkoutSession = $checkoutSession;
        $this->saleData = $saleData;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function action()
    {
        return [
            'items' => $this->quoteWrapDataProvider->getWrapItemsData(),
            'button' => $this->isAddButtonActive()
        ];
    }

    /**
     * @return bool
     */
    public function isAddButtonActive()
    {
        try {
            $result = $this->isProductsForWrapExists();
        } catch (\Exception $e) {
            $result = false;
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
                    && $this->saleData->isItemHasFreeQty($quoteItem)
            ) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
