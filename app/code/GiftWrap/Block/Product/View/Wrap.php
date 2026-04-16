<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Product\View;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\ImageProcessor;
use Amasty\GiftWrap\Model\Price\Renderer as PriceRenderer;
use Amasty\GiftWrap\Model\Price\SalableFactory;
use Amasty\GiftWrap\Model\Wrapper\Resolver as WrapResolver;
use Magento\Framework\View\Element\Template;

class Wrap extends AbstractView
{
    /**
     * @var WrapResolver
     */
    private $wrapResolver;

    /**
     * @var array|null
     */
    protected $wrappers = null;

    public function __construct(
        Template\Context $context,
        ImageProcessor $imageProcessor,
        PriceRenderer $priceRenderer,
        SalableFactory $salableFactory,
        ConfigProvider $configProvider,
        WrapResolver $wrapResolver,
        array $data = []
    ) {
        parent::__construct($context, $imageProcessor, $priceRenderer, $salableFactory, $configProvider, $data);
        $this->wrapResolver = $wrapResolver;
    }

    /**
     * @return array|null
     */
    public function getWrappers()
    {
        if ($this->wrappers === null) {
            $this->wrappers = $this->wrapResolver->getSorted($this->_storeManager->getStore()->getId());
        }

        return $this->wrappers;
    }
}
