<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap as ResourceWrap;
use Amasty\GiftWrap\Model\SaleData\Address\Wrap;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_setIdFieldName(WrapInterface::ID);
        $this->_init(
            Wrap::class,
            ResourceWrap::class
        );
    }
}
