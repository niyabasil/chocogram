<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Product;

use Amasty\GiftWrap\Model\SaleData\AbstractWrap;
use Magento\Catalog\Model\Product as Product;
use Magento\Framework\View\Element\Template;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Amasty\GiftWrap\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var string
     */
    private $visibility;

    /**
     * @var boolean
     */
    private $skipValidation;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var AbstractWrap|null
     */
    private $selectedWrap;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\GiftWrap\Model\ConfigProvider $configProvider,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->configProvider = $configProvider;
        $this->visibility = $data['visibility'] ?? '';
        $this->skipValidation = $data['skip_validation'] ?? false;
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->validate()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return $this->skipValidation || $this->checkForProduct();
    }

    /**
     * @param array $additionalConfig
     * @return string
     */
    public function getJsonConfig($additionalConfig = [])
    {
        $result = [];
        if ($this->getProduct()) {
            $result['currentProductName'] = $this->getProduct()->getName();
        }
        $result = array_merge($result, $additionalConfig);

        return $this->jsonEncoder->encode($result);
    }

    /**
     * @return bool
     */
    private function checkForProduct()
    {
        return $this->configProvider->isEnabledOnPage($this->visibility)
            && $this->enabledForProduct($this->getProduct())
            && $this->getProduct()->isSaleable();
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->registry->registry('product'));
        }

        return $this->getData('product');
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
        return (bool) $product->getAmAvailableForWrapping();
    }

    /**
     * @param AbstractWrap|null $selectedWrap
     * @return $this
     */
    public function setSelectedWrap($selectedWrap)
    {
        $this->selectedWrap = $selectedWrap;
        return $this;
    }

    /**
     * @return AbstractWrap|null
     */
    public function getSelectedWrap()
    {
        return $this->selectedWrap;
    }

    /**
     * @param string $alias
     * @return string
     */
    public function getChildBlockHtml($alias)
    {
        $html = '';
        $block = $this->getChildBlock($alias);
        if ($block) {
            $block->setSelectedWrap($this->getSelectedWrap());
            $html = $block->toHtml();
        }

        return $html;
    }
}
