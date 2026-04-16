<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap;

use Amasty\GiftWrap\Model\SaleData\Quote\Wrap;
use Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap as ResourceWrap;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_setIdFieldName(ResourceWrap::ID);
        $this->_init(
            Wrap::class,
            ResourceWrap::class
        );
    }
}
