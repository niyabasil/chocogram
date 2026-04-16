<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Product\View;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\ImageProcessor;
use Amasty\GiftWrap\Model\MessageCard\Resolver;
use Amasty\GiftWrap\Model\Price\Renderer as PriceRenderer;
use Amasty\GiftWrap\Model\Price\SalableFactory;
use Magento\Framework\View\Element\Template;

class Card extends AbstractView
{
    /**
     * @var array|null
     */
    protected $messageCards = null;

    /**
     * @var Resolver
     */
    private $cardResolver;

    public function __construct(
        Template\Context $context,
        ImageProcessor $imageProcessor,
        PriceRenderer $priceRenderer,
        SalableFactory $salableFactory,
        ConfigProvider $configProvider,
        Resolver $cardResolver,
        array $data = []
    ) {
        parent::__construct($context, $imageProcessor, $priceRenderer, $salableFactory, $configProvider, $data);
        $this->cardResolver = $cardResolver;
    }

    /**
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMessageCards()
    {
        if ($this->messageCards === null) {
            $this->messageCards = $this->cardResolver->getSorted($this->_storeManager->getStore()->getId());
        }

        return $this->messageCards;
    }

    /**
     * @return bool
     */
    public function getIsAllowedMessageWithoutCard()
    {
        return $this->configProvider->getIsAllowedMessageWithoutCard();
    }

    /**
     * @return string
     */
    public function getMessagePlaceholder()
    {
        return $this->configProvider->getGiftMessagePlaceholder();
    }
}
