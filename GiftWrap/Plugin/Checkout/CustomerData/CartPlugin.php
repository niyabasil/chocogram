<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Checkout\CustomerData;

use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;

class CartPlugin
{
    /**
     * @var SaleData
     */
    private $saleData;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(SaleData $saleData, CheckoutSession $checkoutSession)
    {
        $this->saleData = $saleData;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Cart $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(Cart $subject, array $result)
    {
        $existingWraps = $this->saleData->loadWrapsByQuoteId($this->checkoutSession->getQuoteId());
        if ($existingWraps) {
            $result['quote_wrap_data'] = $existingWraps;
        }
        foreach ($this->checkoutSession->getQuote()->getAllItems() as $quoteItem) {
            $result['free_qty_for_wrap'][$quoteItem->getId()] = $this->saleData->isItemHasFreeQty($quoteItem);
        }

        return $result;
    }
}
