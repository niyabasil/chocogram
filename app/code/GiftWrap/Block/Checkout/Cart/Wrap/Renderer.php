<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Checkout\Cart\Wrap;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\OptionSource\Allow;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Renderer extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GiftWrap::checkout/cart/button.phtml';

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SaleData
     */
    private $saleData;

    public function __construct(
        ConfigProvider $configProvider,
        Session $checkoutSession,
        SaleData $saleData,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->configProvider = $configProvider;
        $this->saleData = $saleData;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $result = '';
        if ($this->configProvider->isEnabledOnPage(Allow::CART_PAGE)) {
            $result = parent::_toHtml();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->validate()) {
            return parent::toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        return $this->configProvider->isEnabledOnPage(\Amasty\GiftWrap\Model\OptionSource\Allow::CART_PAGE);
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
            $this->_logger->error($e->getMessage());
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
