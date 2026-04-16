<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Checkout;

/**
 * Used for disable output some totals in totals block
 *
 * Class EmptyTotal
 */
class EmptyTotal extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        return '';
    }
}
