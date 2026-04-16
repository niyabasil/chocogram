<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Model\Order;

use Magento\Sales\Model\Order\Invoice;
use \Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;

class InvoicePlugin
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
     * @param Invoice $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(Invoice $subject, $result)
    {
        $this->saleDataResource->saveData(
            SaleDataResourceInterface::INVOICE_TABLE,
            'invoice_id',
            $subject
        );

        return $result;
    }

    /**
     * @param Invoice $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterLoad(Invoice $subject, $result)
    {
        $this->saleDataResource->loadData(
            SaleDataResourceInterface::INVOICE_TABLE,
            'invoice_id',
            $subject
        );

        return $result;
    }
}
