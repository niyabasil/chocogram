<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddPaymentGiftWrapItem implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $quote = $cart->getSalesModel();
        $total = $quote->getDataUsingMethod('am_gift_wrap_base_total_price');
        if ($total) {
            $cart->addCustomItem(__('Gift Wrapping'), 1, $total);
        }
    }
}
