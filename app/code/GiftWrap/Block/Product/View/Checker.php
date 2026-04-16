<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Product\View;

use Magento\Framework\View\Element\Template;

class Checker extends Template
{
    /**
     * @var \Amasty\GiftWrap\Model\ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        \Amasty\GiftWrap\Model\ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getCustomerNote()
    {
        return strip_tags($this->configProvider->getCustomerNote());
    }
}
