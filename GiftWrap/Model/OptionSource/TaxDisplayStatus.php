<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class TaxDisplayStatus implements OptionSourceInterface
{
    public const EXCLUDE_TAX = 0;
    public const INCLUDE_TAX = 1;
    public const BOTH_PRICES = 2;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::EXCLUDE_TAX, 'label' => __('Excluding Tax')],
            ['value' => self::INCLUDE_TAX, 'label' => __('Including Tax')],
            ['value' => self::BOTH_PRICES, 'label' => __('Including and Excluding Tax')]
        ];
    }
}
