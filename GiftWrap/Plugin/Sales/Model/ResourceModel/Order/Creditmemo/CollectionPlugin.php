<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model\ResourceModel\Order\Creditmemo;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;

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
            foreach ($result as $creditmemo) {
                $this->saleDataResource->loadData(
                    SaleDataResourceInterface::CREDITMEMO_TABLE,
                    'creditmemo_id',
                    $creditmemo
                );
            }

        }

        return $result;
    }
}
