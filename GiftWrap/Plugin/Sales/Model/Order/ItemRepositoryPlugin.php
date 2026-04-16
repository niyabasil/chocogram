<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model\Order;

use Magento\Sales\Model\Order\ItemRepository;
use \Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;

/**
 * Class ItemPlugin
 */
class ItemRepositoryPlugin
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
     * @param ItemRepository $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(ItemRepository $subject, $result)
    {
        if (!$result->isDeleted()) {
            $this->saleDataResource->saveItemsData(
                SaleDataResourceInterface::ORDER_ITEM_TABLE,
                'order_item_id',
                $result
            );
        }

        return $result;
    }

    /**
     * @param ItemRepository $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterLoad(ItemRepository $subject, $result)
    {
        $this->saleDataResource->loadItemsData(
            SaleDataResourceInterface::ORDER_ITEM_TABLE,
            'order_item_id',
            $result
        );

        return $result;
    }

    public function afterGetList(ItemRepository $subject, $result)
    {
        foreach ($result as $item) {
            $this->saleDataResource->loadItemsData(
                SaleDataResourceInterface::ORDER_ITEM_TABLE,
                'order_item_id',
                $item
            );
        }

        return $result;
    }
}
