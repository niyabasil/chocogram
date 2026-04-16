<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model\Cart;

use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;
use Magento\Quote\Model\Cart\TotalsConverter;
use Magento\Quote\Api\Data\TotalSegmentInterface;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Quote\Api\Data\TotalSegmentExtensionInterface;

class TotalsConverterPlugin
{
    /**
     * @var TotalSegmentExtensionFactory
     */
    private $totalSegmentExtensionFactory;

    /**
     * @var string
     */
    private $code;

    /**
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     */
    public function __construct(TotalSegmentExtensionFactory $totalSegmentExtensionFactory)
    {
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->code = 'am_gift_wrap_quote';
    }

    /**
     * Update totals with gift wrapping information
     *
     * @param TotalsConverter $subject
     * @param TotalSegmentInterface[] $result
     * @param QuoteAddressTotal[] $addressTotals
     * @return TotalSegmentInterface[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(TotalsConverter $subject, $result, $addressTotals)
    {
        if (empty($addressTotals[$this->code])) {
            return $result;
        }

        $addressTotal = $addressTotals[$this->code];

        /** @var TotalSegmentExtensionInterface $totalSegmentExtension */
        $totalSegmentExtension = $this->totalSegmentExtensionFactory->create();
        $totalSegmentExtension->setAmGiftWrapTotalPrice($addressTotal->getAmGiftWrapTotalPrice());
        $totalSegmentExtension->setAmGiftWrapBaseTotalPrice($addressTotal->getAmGiftWrapBaseTotalPrice());
        $totalSegmentExtension->setAmGiftWrapTotalTaxAmount($addressTotal->getAmGiftWrapTotalTaxAmount());
        $totalSegmentExtension->setAmGiftWrapBaseTotalTaxAmount($addressTotal->getAmGiftWrapBaseTotalTaxAmount());
        $totalSegmentExtension->setAmGiftWrapTotalPriceInclTax($addressTotal->getAmGiftWrapTotalPriceInclTax());
        $totalSegmentExtension->setAmGiftWrapBaseTotalPriceInclTax($addressTotal->getAmGiftWrapBaseTotalPriceInclTax());
        $totalSegmentExtension->setAmGiftWrapWrapIdsCount($addressTotal->getAmGiftWrapWrapIdsCount());

        $result[$this->code]->setExtensionAttributes($totalSegmentExtension);

        return $result;
    }
}
