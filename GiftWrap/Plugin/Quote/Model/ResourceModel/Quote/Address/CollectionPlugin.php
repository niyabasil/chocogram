<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\ResourceModel\Quote\Address;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address\Collection;

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
            foreach ($result as $addressObject) {
                $this->saleDataResource->loadData(
                    SaleDataResourceInterface::QUOTE_ADDRESS_TABLE,
                    'quote_address_id',
                    $addressObject
                );
                $this->saleDataResource->loadItemsData(
                    SaleDataResourceInterface::QUOTE_ADDRESS_WRAP_TABLE,
                    'quote_address_id',
                    $addressObject
                );
            }
        }

        return $result;
    }
}
