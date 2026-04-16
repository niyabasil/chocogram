<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement as AbstractElement;

class Enabled extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($this->moduleManager->isEnabled('Amasty_Checkout')) {
            $element->setComment(
                $element->getComment()
                . ' Note: if set to ‘Yes’, the Gift Wrap’s functionality will overwrite Gifts tab settings'
                . ' of One Step Checkout extension.'
            );
        }

        return parent::render($element);
    }
}
