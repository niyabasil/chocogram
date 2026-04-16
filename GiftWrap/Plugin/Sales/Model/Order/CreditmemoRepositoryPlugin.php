<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model\Order;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\CreditmemoRepository;

class CreditmemoRepositoryPlugin
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
     * @param CreditmemoRepository $subject
     * @param Creditmemo $result
     * @return mixed
     */
    public function afterSave(CreditmemoRepository $subject, Creditmemo $result)
    {
        $this->saleDataResource->saveData(
            SaleDataResourceInterface::CREDITMEMO_TABLE,
            'creditmemo_id',
            $result
        );

        return $result;
    }

    /**
     * @param CreditmemoRepository $subject
     * @param Creditmemo $result
     * @return mixed
     */
    public function afterLoad(CreditmemoRepository $subject, Creditmemo $result)
    {
        $this->saleDataResource->loadData(
            SaleDataResourceInterface::CREDITMEMO_TABLE,
            'creditmemo_id',
            $result
        );

        return $result;
    }
}
