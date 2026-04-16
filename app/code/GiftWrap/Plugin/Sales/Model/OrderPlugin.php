<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use \Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;

/**
 * Class QuotePlugin
 */
class OrderPlugin
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
     * @param Order $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(Order $subject, $result)
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
     * @param Order $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterLoad(Order $subject, $result)
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
     * @param Quote $subject
     * @param Collection $result
     * @return Quote
     */
    public function afterGetItemsCollection(Order $subject, Collection $result)
    {
        if (!$subject->getIsAdditionalDataLoaded() && $result->isLoaded()) {
            foreach ($result->getItems() as $item) {
                $this->saleDataResource->loadItemsData(
                    SaleDataResourceInterface::ORDER_ITEM_TABLE,
                    'order_item_id',
                    $item
                );
            }
            $subject->setIsAdditionalDataLoaded(true);
        }

        return $result;
    }
}
