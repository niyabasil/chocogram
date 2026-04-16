<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Checkout\Controller\Cart;

class IndexPlugin
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(\Magento\Checkout\Model\Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * Disable multi-shipping
     *
     * @param \Magento\Framework\App\Action\Action $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(\Magento\Framework\App\Action\Action $subject)
    {
        if ($this->cart->getQuote()->getIsMultiShipping()) {
            $this->cart->getQuote()->setIsMultiShipping(0);
            $extensionAttributes = $this->cart->getQuote()->getExtensionAttributes();
            if ($extensionAttributes && $extensionAttributes->getShippingAssignments()) {
                $extensionAttributes->setShippingAssignments([]);
            }
            $this->cart->save();
        }
    }
}
