<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Address;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\SaleData\AbstractWrap;
use Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap as ResourceWrap;

class Wrap extends AbstractWrap implements WrapInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceWrap::class);
        $this->setIdFieldName(WrapInterface::ID);
    }
}
