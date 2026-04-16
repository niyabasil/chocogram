<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model;

use Magento\Directory\Model\Currency;
use Magento\Store\Model\ScopeInterface;

class PriceConverter
{
    /**
     * @var \Magento\Directory\Model\PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $price
     * @param $scope
     * @param null|Currency|string $currency
     * @return float|string
     */
    public function convertPrice($price, $scope = null, $currency = null)
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            false,
            \Magento\Directory\Model\PriceCurrency::DEFAULT_PRECISION,
            $scope,
            $currency
        );
    }

    /**
     * @return string
     */
    public function getDisplayCurrency()
    {
        return $this->scopeConfig->getValue(
            Currency::XML_PATH_CURRENCY_DEFAULT,
            ScopeInterface::SCOPE_STORE,
            null
        );
    }
}
