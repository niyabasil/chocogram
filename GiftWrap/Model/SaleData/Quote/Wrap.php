<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Quote;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\SaleData\AbstractWrap;
use Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap as ResourceWrap;

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
