<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Total\Order\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;

class GiftWrap extends DefaultTotal
{
    /**
     * @inheritdoc
     */
    public function getTotalsForDisplay()
    {
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        if ($this->getSource()->getWrapItems() || ($this->getOrder() && $this->getOrder()->getWrapItems())) {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __($this->getTitle()) . ':',
                    'font_size' => $fontSize,
                ],
            ];
        } else {
            $totals = [];
        }

        return $totals;
    }
}
