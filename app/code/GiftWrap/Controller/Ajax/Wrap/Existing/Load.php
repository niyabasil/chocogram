<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Ajax\Wrap\Existing;

use Amasty\GiftWrap\Block\Wrap\Existing\Content;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\View\Layout;

class Load extends Action
{
    /**
     * @var Layout
     */
    private $layout;

    public function __construct(
        Layout $layout,
        Context $context
    ) {
        parent::__construct($context);
        $this->layout = $layout;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $wrapsBlock = $this->layout->createBlock(Content::class);

        return $resultJson->setData(['html' => $wrapsBlock->toHtml()]);
    }
}
