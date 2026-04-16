<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\QuoteRepository;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository\SaveHandler;

class SaveHandlerPlugin
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
     * @param SaveHandler $subject
     * @param CartInterface|Quote $quote
     * @return CartInterface
     */
    public function afterSave(SaveHandler $subject, CartInterface $quote)
    {
        $this->saleDataResource->saveItemsData(
            SaleDataResourceInterface::QUOTE_WRAP_TABLE,
            'quote_id',
            $quote
        );

        return $quote;
    }
}
