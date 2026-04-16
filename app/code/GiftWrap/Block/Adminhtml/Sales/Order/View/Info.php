<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Adminhtml\Sales\Order\View;

use Amasty\GiftWrap\Block\Adminhtml\Sales\View\GiftWrapAbstaract;

class Info extends GiftWrapAbstaract
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GiftWrap::sales/order/view/info.phtml';

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->order) {
            $this->setOrder($this->getRegistry()->registry('sales_order'));
        }
        return $this->order;
    }
}
