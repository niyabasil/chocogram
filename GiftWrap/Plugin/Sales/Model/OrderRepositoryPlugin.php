<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class OrderRepositoryPlugin
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
     * @param OrderRepository $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(OrderRepository $subject, $result)
    {
        $this->saleDataResource->saveData(
            SaleDataResourceInterface::ORDER_TABLE,
            'order_id',
            $result
        );
        $this->saleDataResource->saveItemsData(
            SaleDataResourceInterface::ORDER_WRAP_TABLE,
            'order_id',
            $result
        );

        return $result;
    }

    /**
     * @param OrderRepository $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGet(OrderRepository $subject, $result)
    {
        $this->saleDataResource->loadData(
            SaleDataResourceInterface::ORDER_TABLE,
            'order_id',
            $result
        );
        $this->saleDataResource->loadItemsData(
            SaleDataResourceInterface::ORDER_WRAP_TABLE,
            'order_id',
            $result
        );

        return $result;
    }

    /**
     * @param OrderRepository $subject
     * @param OrderSearchResultInterface $result
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepository $subject, OrderSearchResultInterface $result)
    {
        foreach ($result->getItems() as $order) {
            $this->saleDataResource->loadData(
                SaleDataResourceInterface::ORDER_TABLE,
                'order_id',
                $order
            );
            $this->saleDataResource->loadItemsData(
                SaleDataResourceInterface::ORDER_WRAP_TABLE,
                'order_id',
                $order
            );
        }

        return $result;
    }
}
