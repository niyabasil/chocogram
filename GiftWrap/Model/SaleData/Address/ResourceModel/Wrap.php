<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Address\ResourceModel;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Wrap extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SaleDataResourceInterface::QUOTE_ADDRESS_WRAP_TABLE, WrapInterface::ID);
    }
}
