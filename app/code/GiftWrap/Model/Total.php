<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model;

class Total
{
    public const WRAP_TOGETHER = true;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(\Amasty\GiftWrap\Model\ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param  \Magento\Framework\DataObject $dataObject
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getTotals($dataObject)
    {
        $totals = [];

        $displayWrappIncludeTaxPrice = $this->configProvider->getDisplayTotalsInclTax();
        $displayWrapBothPrices = $this->configProvider->getDisplayTotalsBothPrices();

        /**
         * Gift wrapping for items totals
         */
        if ($displayWrapBothPrices || $displayWrappIncludeTaxPrice) {
            $this->addTotalToTotals(
                $totals,
                'am_gift_wrap_total_incl',
                $dataObject->getAmGiftWrapTotalPrice() + $dataObject->getAmGiftWrapTotalTaxAmount(),
                $dataObject->getAmGiftWrapTotalBasePrice() + $dataObject->getAmGiftWrapBaseTotalTaxAmount(),
                __('Gift Wrap (Incl. Tax)')
            );
            if ($displayWrapBothPrices) {
                $this->addTotalToTotals(
                    $totals,
                    'am_gift_wrap_total_excl',
                    $dataObject->getAmGiftWrapTotalPrice(),
                    $dataObject->getAmGiftWrapBaseTotalPrice(),
                    __('Gift Wrap (Excl. Tax)')
                );
            }
        } else {
            $this->addTotalToTotals(
                $totals,
                'am_gift_wrap_total',
                $dataObject->getAmGiftWrapTotalPrice(),
                $dataObject->getAmGiftWrapBaseTotalPrice(),
                __('Gift Wrap')
            );
        }

        return $totals;
    }

    /**
     * @param  array &$totals
     * @param  string $code
     * @param  float $value
     * @param  float $baseValue
     * @param  string $label
     * @return void
     */
    private function addTotalToTotals(&$totals, $code, $value, $baseValue, $label)
    {
        $total = ['code' => $code, 'value' => $value, 'base_value' => $baseValue, 'label' => $label];
        $totals[] = $total;
    }
}
