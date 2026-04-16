<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Checkout\Item\Wrap;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\OptionSource\Allow;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Renderer extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GiftWrap::checkout/cart/item_button.phtml';

    /**
     * @var AbstractItem
     */
    private $item;

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var SaleData
     */
    private $saleData;

    public function __construct(
        SaleData $saleData,
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->saleData = $saleData;
    }

    /**
     * Set item for render
     *
     * @param AbstractItem $item
     * @return $this
     */
    public function setItem(AbstractItem $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * @return AbstractItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->configProvider->isEnabledOnPage(Allow::CART_PAGE)
            && $this->enabledForProduct($this->getItem()->getProduct())
        ) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    protected function enabledForProduct(Product $product)
    {
        // wrap works only for giftcard with type TYPE_PHYSICAL
        if (($product->getTypeId() === 'giftcard' && $product->getGiftcardType() !== '1')
            || ($product->getTypeId() === 'amgiftcard' && $product->getAmGiftcardType() === '1')
        ) {
            return false;
        }

        return !!$product->getData(CreateProductAttributes::AVAILABLE_FOR_WRAPPING);
    }

    /**
     * @return bool
     */
    public function isItemHasFreeQty()
    {
        return $this->saleData->isItemHasFreeQty($this->getItem());
    }
}
