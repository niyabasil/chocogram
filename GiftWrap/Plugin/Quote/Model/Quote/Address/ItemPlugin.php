<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\Quote\Address;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Quote\Model\Quote\Address\Item;

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
                SaleDataResourceInterface::QUOTE_ADDRESS_ITEM_TABLE,
                'quote_item_id',
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
            SaleDataResourceInterface::QUOTE_ADDRESS_ITEM_TABLE,
            'quote_item_id',
            $subject
        );

        return $result;
    }
}
