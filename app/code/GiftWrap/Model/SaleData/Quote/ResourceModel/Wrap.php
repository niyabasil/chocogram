<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Wrap extends AbstractDb
{
    public const ID = 'id';
    public const WRAP_ID = 'wrap_id';
    public const CARD_ID = 'card_id';
    public const RECIPIENT_HIDDEN = 'is_receipt_hidden';
    public const GIFT_MESSAGE = 'gift_message';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SaleDataResourceInterface::QUOTE_WRAP_TABLE, WrapInterface::ID);
    }
}
