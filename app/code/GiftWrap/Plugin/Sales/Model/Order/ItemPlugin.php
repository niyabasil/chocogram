<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model\Order;

use Magento\Sales\Model\Order\Item;
use \Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;

class ItemPlugin
{
    /**
     * @var SaleDataResourceInterface
     */
    private $saleDataResource;

    public function __construct(
        SaleDataResourceInterface $saleDataResource
    ) {
        $this->saleDataResource = $saleDataResource;
    }

    /**
     * @param Item $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(Item $subject, $result)
    {
        if (!$result->isDeleted()) {
            $this->saleDataResource->saveItemsData(
                SaleDataResourceInterface::ORDER_ITEM_TABLE,
                'order_item_id',
                $subject
            );
        }

        return $result;
    }

    /**
     * @param Item $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterLoad(Item $subject, $result)
    {
        $this->saleDataResource->loadItemsData(
            SaleDataResourceInterface::ORDER_ITEM_TABLE,
            'order_item_id',
            $subject
        );

        return $result;
    }
}
