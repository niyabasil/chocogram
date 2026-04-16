<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Checkout\Cart;

use Amasty\GiftWrap\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;

class WrapTitle extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function getCustomerNoteHtml(): string
    {
        return preg_replace(
            '/[\n\r]/',
            '',
            nl2br($this->_escaper->escapeHtml($this->configProvider->getCustomerNote()))
        );
    }
}
