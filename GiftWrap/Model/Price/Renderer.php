<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Price;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;

class Renderer
{
    public const PRICE_BLOCK_NAME = 'amgiftwrap.price';
    public const SALABLE_ITEMS_KEY = 'salable_items';

    /**
     * @var LayoutInterface
     */
    private $layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @param SalableInterface[] $salableItems
     * @return string
     */
    public function execute(array $salableItems): string
    {
        /** @var Template $priceBlock */
        $priceBlock = $this->layout->getBlock(self::PRICE_BLOCK_NAME);

        if ($priceBlock) {
            $priceBlock->setData(self::SALABLE_ITEMS_KEY, $salableItems);
            $result = $priceBlock->toHtml();
        } else {
            $result = '';
        }

        return $result;
    }
}
