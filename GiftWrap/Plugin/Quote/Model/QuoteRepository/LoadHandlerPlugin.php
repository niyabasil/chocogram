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
use Magento\Quote\Model\QuoteRepository\LoadHandler;

class LoadHandlerPlugin
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
     * @param LoadHandler $subject
     * @param CartInterface|Quote $quote
     * @return CartInterface
     */
    public function afterLoad(LoadHandler $subject, CartInterface $quote)
    {
        $this->saleDataResource->loadItemsData(
            SaleDataResourceInterface::QUOTE_WRAP_TABLE,
            'quote_id',
            $quote
        );

        return $quote;
    }
}
