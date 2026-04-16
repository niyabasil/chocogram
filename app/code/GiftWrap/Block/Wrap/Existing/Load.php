<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Wrap\Existing;

use Magento\Framework\View\Element\Template;

class Load extends Template
{
    /**
     * @return string
     */
    public function getLoadUrl()
    {
        return $this->_urlBuilder->getUrl('amgiftwrap/ajax_wrap_existing/load');
    }
}
