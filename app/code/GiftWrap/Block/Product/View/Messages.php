<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Product\View;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;

class Messages extends Template
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param Phrase $message
     */
    public function addError(Phrase $message)
    {
        $this->errors[] = $message;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $errors = $this->errors;
        $this->errors = [];

        return $errors;
    }
}
