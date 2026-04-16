<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Product\View;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\ImageProcessor;
use Amasty\GiftWrap\Model\MessageCard\MessageCard;
use Amasty\GiftWrap\Model\Price\Renderer as PriceRenderer;
use Amasty\GiftWrap\Model\Price\SalableFactory;
use Amasty\GiftWrap\Model\Wrapper\Wrap;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Element\Template;

class AbstractView extends Template
{
    public const IMAGE_WIDTH = 100;

    public const IMAGE_HEIGHT = 100;

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var PriceRenderer
     */
    private $priceRenderer;

    /**
     * @var SalableFactory
     */
    private $salableFactory;

    public function __construct(
        Template\Context $context,
        ImageProcessor $imageProcessor,
        PriceRenderer $priceRenderer,
        SalableFactory $salableFactory,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->imageProcessor = $imageProcessor;
        $this->configProvider = $configProvider;
        $this->priceRenderer = $priceRenderer;
        $this->salableFactory = $salableFactory;
    }

    /**
     * @param $image
     *
     * @return string
     */
    public function getImageSrc($image)
    {
        return $this->imageProcessor->getResizedUrl($image, self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
    }

    /**
     * @param Wrap|MessageCard|AbstractModel $model
     * @return string
     */
    public function getPriceHtml(AbstractModel $model): string
    {
        $salableItem = $this->salableFactory->create($model);
        return $this->priceRenderer->execute([$salableItem]);
    }
}
