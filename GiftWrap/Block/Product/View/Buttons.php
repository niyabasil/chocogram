<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Product\View;

use Magento\Framework\View\Element\Template;

class Buttons extends Template
{
    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl('amgiftwrap/ajax_wrap/add');
    }

    /**
     * @return string
     */
    public function getUpdateWrapUrl()
    {
        return $this->_urlBuilder->getUrl('amgiftwrap/ajax_wrap/update');
    }

    /**
     * @return bool
     */
    public function isSaveAjax()
    {
        return $this->_data['save_ajax'] ?? false;
    }
}
