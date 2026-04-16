<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class Allow implements OptionSourceInterface
{
    public const PRODUCT_PAGE = 'product';

    public const CART_PAGE = 'cart';

    public const CHECKOUT_PAGE = 'checkout';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PRODUCT_PAGE, 'label' => __('Product Page')],
            ['value' => self::CART_PAGE, 'label' => __('Shopping Cart Page')],
            ['value' => self::CHECKOUT_PAGE, 'label' => __('Checkout Page')]
        ];
    }
}
