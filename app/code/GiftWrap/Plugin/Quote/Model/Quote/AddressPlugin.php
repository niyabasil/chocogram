<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\Quote;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Quote\Model\Quote\Address;

class AddressPlugin
{
    /**
     * @var SaleDataResourceInterface
     */
    private $saleDataResource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        SaleDataResourceInterface $saleDataResource,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->saleDataResource = $saleDataResource;
        $this->logger = $logger;
    }

    /**
     * @param Address $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(Address $subject, $result)
    {
        try {
            $this->saleDataResource->saveData(
                SaleDataResourceInterface::QUOTE_ADDRESS_TABLE,
                'quote_address_id',
                $subject
            );
            $this->saleDataResource->saveItemsData(
                SaleDataResourceInterface::QUOTE_ADDRESS_WRAP_TABLE,
                'quote_address_id',
                $subject
            );
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $result;
    }

    /**
     * @param Address $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterLoad(Address $subject, $result)
    {
        $this->saleDataResource->loadData(
            SaleDataResourceInterface::QUOTE_ADDRESS_TABLE,
            'quote_address_id',
            $subject
        );
        $this->saleDataResource->loadItemsData(
            SaleDataResourceInterface::QUOTE_ADDRESS_WRAP_TABLE,
            'quote_address_id',
            $subject
        );

        return $result;
    }
}
