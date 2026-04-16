<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Checkout\Model\Gift;

use Amasty\GiftWrap\Model\ConfigProvider;

class MessagesPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetGiftMessages($subject, callable $proceed)
    {
        if ($this->configProvider->isEnabled()) {
            return [];
        }

        return $proceed();
    }
}
