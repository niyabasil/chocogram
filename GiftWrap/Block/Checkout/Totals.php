<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Checkout;

use Magento\Quote\Model\Quote\Address;

class Totals extends \Magento\Checkout\Block\Total\DefaultTotal
{
    /**
     * @var \Amasty\GiftWrap\Model\Total
     */
    private $totalProvider;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        \Amasty\GiftWrap\Model\Total $totalProvider,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
        $this->totalProvider = $totalProvider;
        $this->_isScopePrivate = true;
        $this->setTemplate('checkout/totals.phtml');
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Return information for showing
     *
     * @return array
     */
    public function getValues()
    {
        $values = [];
        $total = $this->getTotal();
        $address = $total->getAddress();
        if ($this->isAvailable($address)) {
            $totals = $this->totalProvider->getTotals($total);
            foreach ($totals as $total) {
                $label = (string)$total['label'];
                $values[$label] = $total['value'];
            }
        }

        return $values;
    }

    /**
     * @param float $value
     *
     * @return string
     */
    public function formatPrice($value)
    {
        return $this->checkoutHelper->formatPrice($value);
    }

    /**
     * @param Address $address
     * @return bool
     */
    private function isAvailable(Address $address)
    {
        $result = false;

        $wrapItems = $address->getQuote()->getIsMultiShipping()
            ? $address->getWrapItems()
            : $address->getQuote()->getWrapItems();

        foreach ($wrapItems as $wrapItem) {
            if (!$wrapItem->getIsDeleted()) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
