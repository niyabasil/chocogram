<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Quote\Tax;

use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Amasty\GiftWrap\Model\Total\Quote\Tax\BeforeTax;

class AfterTax extends AbstractTotal
{
    /**
     * @var bool
     */
    private $oneWrapPerAddress = false;

    public function __construct()
    {
        $this->setCode('am_gift_wrap_tax_after');
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Address\Total $total
    ) {
        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() !== Address::TYPE_SHIPPING) {
            return $this;
        }

        $extraTaxableDetails = $total->getExtraTaxableDetails();
        if (!$extraTaxableDetails) {
            $extraTaxableDetails = [];
        }

        if ($quote->getIsMultiShipping()
            && $shippingAssignment->getShipping()->getAddress()->getItemsCollection()->getSize()) {
            $this->oneWrapPerAddress = true;
        }

        if ($this->oneWrapPerAddress) {
            $this->processItemsForQuote(
                $total,
                $this->getItemTaxDetails($extraTaxableDetails, BeforeTax::WRAP_TYPE)
            );

            $this->processItemsForQuote(
                $total,
                $this->getItemTaxDetails($extraTaxableDetails, BeforeTax::CODE_CARD)
            );
        } else {
            $this->processItems(
                $total,
                $this->getItemTaxDetails($extraTaxableDetails, BeforeTax::WRAP_TYPE)
            );

            $this->processCards(
                $total,
                $this->getItemTaxDetails($extraTaxableDetails, BeforeTax::CODE_CARD)
            );
        }

        return $this;
    }

    /**
     * @param Address\Total $total
     * @param array $itemTaxDetails
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function processItems($total, $itemTaxDetails)
    {
        $codeMappimg = $total->getAmastyGiftWrapCodeMapping();
        $baseTotalTaxAmount = null;
        $totalTaxAmount = null;
        $totalPriceInclTax = null;
        $baseTotalPriceInclTax = null;

        if (!empty($itemTaxDetails)) {
            foreach ($itemTaxDetails as $itemCode => $itemTaxDetail) {
                $itemTaxDetailCount = is_array($itemTaxDetails) ? count($itemTaxDetail) : 0;
                if ($itemTaxDetailCount < 1) {
                    continue;
                }

                for ($i = 0; $i < $itemTaxDetailCount; $i++) {
                    $wrapTaxDetail = $itemTaxDetail[$i];
                    $wrapItemCode = $wrapTaxDetail['code'];

                    if (!array_key_exists($wrapItemCode, $codeMappimg)) {
                        continue;
                    }
                    $item = $codeMappimg[$wrapItemCode];

                    if ($item) {
                        $baseTaxAmount = $wrapTaxDetail['base_row_tax'];
                        $taxAmount = $wrapTaxDetail['row_tax'];
                        $totalPriceInclTax += $wrapTaxDetail['price_incl_tax'];
                        $baseTotalPriceInclTax += $wrapTaxDetail['base_price_incl_tax'];

                        $item->setAmGiftWrapBaseTaxAmount($baseTaxAmount);
                        $item->setAmGiftWrapTaxAmount($taxAmount);
                        $item->setAmGiftWrapPriceInclTax($item->getAmGiftWrapPrice() + $taxAmount);
                        $item->setAmGiftWrapBasePriceInclTax($item->getAmGiftWrapBasePrice() + $baseTaxAmount);

                        $baseTotalTaxAmount += $baseTaxAmount;
                        $totalTaxAmount += $taxAmount;
                    }
                }
            }
        }

        $total->setAmGiftWrapBaseTotalTaxAmount($baseTotalTaxAmount);
        $total->setAmGiftWrapTotalTaxAmount($totalTaxAmount);
        $total->setAmGiftWrapTotalPriceInclTax($totalPriceInclTax);
        $total->setAmGiftWrapBaseTotalPriceInclTax($baseTotalPriceInclTax);
        return $this;
    }

    /**
     * @param Address\Total $total
     * @param array $itemTaxDetails
     * @return $this
     */
    private function processCards($total, $itemTaxDetails)
    {
        $codeMappimg = $total->getAmastyGiftWrapCardCodeMapping();
        $baseTotalTaxAmount = null;
        $totalTaxAmount = null;
        $totalPriceInclTax = null;
        $baseTotalPriceInclTax = null;

        if (!empty($itemTaxDetails)) {
            foreach ($itemTaxDetails as $itemCode => $itemTaxDetail) {
                $itemTaxDetailCount = is_array($itemTaxDetails) ? count($itemTaxDetail) : 0;
                if ($itemTaxDetailCount < 1) {
                    continue;
                }

                for ($i = 0; $i < $itemTaxDetailCount; $i++) {
                    $cardTaxDetail = $itemTaxDetail[$i];
                    $wrapItemCode = $cardTaxDetail['code'];

                    if (!array_key_exists($wrapItemCode, $codeMappimg)) {
                        continue;
                    }
                    $item = $codeMappimg[$wrapItemCode];

                    if ($item) {
                        $baseTaxAmount = $cardTaxDetail['base_row_tax'];
                        $taxAmount = $cardTaxDetail['row_tax'];
                        $totalPriceInclTax += $cardTaxDetail['price_incl_tax'];
                        $baseTotalPriceInclTax += $cardTaxDetail['base_price_incl_tax'];

                        $item->setAmGiftWrapBaseCardTaxAmount($baseTaxAmount);
                        $item->setAmGiftWrapCardTaxAmount($taxAmount);
                        $item->setAmGiftWrapCardPriceInclTax($item->getAmGiftWrapCardPrice() + $taxAmount);
                        $item->setAmGiftWrapBaseCardPriceInclTax($item->getAmGiftWrapBaseCardPrice() + $baseTaxAmount);

                        $baseTotalTaxAmount += $baseTaxAmount;
                        $totalTaxAmount += $taxAmount;
                    }
                }
            }
        }

        $total->setAmGiftWrapBaseTotalTaxAmount($total->getAmGiftWrapBaseTotalTaxAmount() + $baseTotalTaxAmount);
        $total->setAmGiftWrapTotalTaxAmount($total->getAmGiftWrapTotalTaxAmount() + $totalTaxAmount);
        $total->setAmGiftWrapTotalPriceInclTax($total->getAmGiftWrapTotalPriceInclTax() + $totalPriceInclTax);
        $total->setAmGiftWrapBaseTotalPriceInclTax(
            $total->getAmGiftWrapBaseTotalPriceInclTax() + $baseTotalPriceInclTax
        );
        return $this;
    }

    /**
     * @param $total
     * @param $itemTaxDetails
     * @return $this
     */
    private function processItemsForQuote($total, $itemTaxDetails)
    {
        $totalPriceInclTax = null;
        $baseTotalPriceInclTax = null;
        $baseTotalTaxAmount = null;
        $totalTaxAmount = null;

        if (!empty($itemTaxDetails)) {
            //there is only one gift wrapping per quote
            $wrapDetail = $itemTaxDetails[CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE][0];
            if (!empty($wrapDetail)) {
                $baseTotalTaxAmount = $wrapDetail['base_row_tax'];
                $totalTaxAmount = $wrapDetail['row_tax'];
                $totalPriceInclTax = $wrapDetail['price_incl_tax'];
                $baseTotalPriceInclTax = $wrapDetail['base_price_incl_tax'];
            }
        }

        $total->setAmGiftWrapBaseTotalTaxAmount($total->getAmGiftWrapBaseTotalTaxAmount() + $baseTotalTaxAmount);
        $total->setAmGiftWrapTotalTaxAmount($total->getAmGiftWrapTotalTaxAmount() + $totalTaxAmount);
        $total->setAmGiftWrapTotalPriceInclTax($total->getAmGiftWrapTotalPriceInclTax() + $totalPriceInclTax);
        $total->setAmGiftWrapBaseTotalPriceInclTax(
            $total->getAmGiftWrapBaseTotalPriceInclTax() + $baseTotalPriceInclTax
        );
        return $this;
    }

    /**
     * @param array $extraTaxableDetails
     * @param string $itemType
     * @return array
     */
    private function getItemTaxDetails(array $extraTaxableDetails, $itemType)
    {
        return isset($extraTaxableDetails[$itemType]) ? $extraTaxableDetails[$itemType] : [];
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => 'am_gift_wrap_tax_after',
            'title' => __('Gift Wrapping'),
            'am_gift_wrap_total_price' => $total->getAmGifWrapTotalPrice(),
            'am_gift_wrap_items_base_price' => $total->getAmGiftWrapBaseTotalPrice(),
            'am_gift_wrap_total_tax_amount' => $total->getAmGiftWrapTotalTaxAmount(),
            'am_gift_wrap_base_total_tax_amount' => $total->getAmGiftWrapBaseTotalTaxAmount(),
            'am_gift_wrap_total_price_incl_tax' => $total->getAmGiftWrapTotalPriceInclTax(),
            'am_gift_wrap_base_total_price_incl_tax' => $total->getAmGiftWrapBaseTotalPriceInclTax(),
        ];
    }
}
