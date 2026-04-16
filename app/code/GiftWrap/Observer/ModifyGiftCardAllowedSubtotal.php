<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * amgcard_allowed_subtotal_calculated
 */
class ModifyGiftCardAllowedSubtotal implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        if (!$this->scopeConfig->isSetFlag('amgiftcard/general/allow_to_paid_for_gift_wrap')) {
            return;
        }
        $event = $observer->getEvent();
        $fromObject = $event->getData('from_object');
        $value = $event->getData('value');

        $value += (float)$fromObject->getAmGiftWrapBaseTotalPrice();
        $event->setData('value', $value);
    }
}
