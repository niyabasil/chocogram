<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\ViewModel;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\Price\Calculation\GetTaxPrice;
use Amasty\GiftWrap\Model\Price\SalableInterface;
use Amasty\GiftWrap\Model\PriceConverter;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class PriceOutput implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetTaxPrice
     */
    private $getTaxPrice;

    /**
     * @var PriceConverter
     */
    private $priceConverter;

    public function __construct(
        ConfigProvider $configProvider,
        GetTaxPrice $getTaxPrice,
        PriceConverter $priceConverter
    ) {
        $this->configProvider = $configProvider;
        $this->getTaxPrice = $getTaxPrice;
        $this->priceConverter = $priceConverter;
    }

    public function isIncludingTaxShow(): bool
    {
        return $this->configProvider->isDisplayInclTax() || $this->configProvider->isDisplayBothPrices();
    }

    public function isExcludingTaxShow(): bool
    {
        return $this->configProvider->isDisplayExclTax() || $this->configProvider->isDisplayBothPrices();
    }

    /**
     * @param SalableInterface[] $salableItems
     * @param bool $withTax
     * @return string
     */
    public function getPrice(array $salableItems, bool $withTax = false): float
    {
        $price = 0.0;

        foreach ($salableItems as $salableItem) {
            if ($withTax) {
                $price += $this->getTaxPrice->execute($salableItem);
            } else {
                $price += $salableItem->getPrice();
            }
        }

        return $price;
    }

    public function convertPrice(float $price): string
    {
        return $this->priceConverter->convertPrice($price);
    }
}
