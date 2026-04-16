<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model\Order;

use Magento\Sales\Model\Order\Creditmemo;
use \Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;

class CreditmemoPlugin
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
     * @param Creditmemo $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(Creditmemo $subject, $result)
    {
        $this->saleDataResource->saveData(
            SaleDataResourceInterface::CREDITMEMO_TABLE,
            'creditmemo_id',
            $subject
        );

        return $result;
    }

    /**
     * @param Creditmemo $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterLoad(Creditmemo $subject, $result)
    {
        $this->saleDataResource->loadData(
            SaleDataResourceInterface::CREDITMEMO_TABLE,
            'creditmemo_id',
            $subject
        );

        return $result;
    }
}
