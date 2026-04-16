<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model\ResourceModel\Order\Invoice;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;

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
            foreach ($result as $invoice) {
                $this->saleDataResource->loadData(
                    SaleDataResourceInterface::INVOICE_TABLE,
                    'invoice_id',
                    $invoice
                );
            }
        }

        return $result;
    }
}
