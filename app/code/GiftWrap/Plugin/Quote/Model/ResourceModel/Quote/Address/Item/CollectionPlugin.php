<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\ResourceModel\Quote\Address\Item;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address\Item\Collection;

class CollectionPlugin
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
     * @param Collection $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterLoad(Collection $subject, Collection $result)
    {
        if (!$result->getFlag('am_wrap_data_loaded')) {
            $result->setFlag('am_wrap_data_loaded', true);
            foreach ($result as $addressItemObject) {
                $this->saleDataResource->loadItemsData(
                    SaleDataResourceInterface::QUOTE_ADDRESS_ITEM_TABLE,
                    'quote_item_id',
                    $addressItemObject
                );
            }
        }

        return $result;
    }
}
