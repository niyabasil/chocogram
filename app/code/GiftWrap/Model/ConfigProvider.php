<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model;

use Amasty\GiftWrap\Model\OptionSource\TaxDisplayStatus;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    public const COLLECT_TOGETHER = true;

    /**
     * @var string
     */
    protected $pathPrefix = 'amgiftwrap/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    public const XPATH_ENABLED = 'general/enabled';
    public const XPATH_ALLOW = 'general/allow';
    public const XPATH_NOTE = 'general/customer_note';
    public const XPATH_WRAP_TAX_CLASS_ID = 'tax/wrap_tax_class_id';
    public const XPATH_CARD_TAX_CLASS_ID = 'tax/card_tax_class_id';
    public const XPATH_WRAP_TAX_DISPLAY_MODE = 'tax/wrap_display_mode';
    public const XPATH_WRAP_TAX_DISPLAY_MODE_TOTALS = 'tax/wrap_display_mode_totals';
    public const XPATH_ALLOW_MESSAGE_WITHOUT_CARD = 'general/allow_message_without_card';
    public const XPATH_GIFT_MESSAGE_PLACEHOLDER = 'general/gift_message_placeholder';
    public const XPATH_ADD_OPTIONS_TO_EMAIL = 'email/add_options_to_email';

    /**
     * @return bool
     */
    public function getDisplayTotalsBothPrices()
    {
        return $this->getValue(self::XPATH_WRAP_TAX_DISPLAY_MODE_TOTALS) == TaxDisplayStatus::BOTH_PRICES;
    }

    /**
     * @return bool
     */
    public function getDisplayTotalsInclTax()
    {
        return $this->getValue(self::XPATH_WRAP_TAX_DISPLAY_MODE_TOTALS) == TaxDisplayStatus::INCLUDE_TAX;
    }

    public function isDisplayBothPrices(): bool
    {
        return $this->getValue(self::XPATH_WRAP_TAX_DISPLAY_MODE) == TaxDisplayStatus::BOTH_PRICES;
    }

    public function isDisplayInclTax(): bool
    {
        return $this->getValue(self::XPATH_WRAP_TAX_DISPLAY_MODE) == TaxDisplayStatus::INCLUDE_TAX;
    }

    public function isDisplayExclTax(): bool
    {
        return $this->getValue(self::XPATH_WRAP_TAX_DISPLAY_MODE) == TaxDisplayStatus::EXCLUDE_TAX;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::XPATH_ENABLED);
    }

    public function isEnabledOnPage(string $page, ?int $storeId = null): bool
    {
        $value = (string)$this->getValue(self::XPATH_ALLOW, $storeId);
        $value = explode(',', $value);

        return $this->isEnabled() && in_array($page, $value);
    }

    /**
     * @return string
     */
    public function getCustomerNote()
    {
        return $this->getValue(self::XPATH_NOTE);
    }

    /**
     * @return int
     */
    public function getWrapTaxClassId()
    {
        return $this->getValue(self::XPATH_WRAP_TAX_CLASS_ID);
    }

    /**
     * @return int
     */
    public function getCardTaxClassId()
    {
        return $this->getValue(self::XPATH_CARD_TAX_CLASS_ID);
    }

    /**
     * @return bool
     */
    public function getIsAllowedMessageWithoutCard()
    {
        return (bool)$this->getValue(self::XPATH_ALLOW_MESSAGE_WITHOUT_CARD);
    }

    /**
     * @return string
     */
    public function getGiftMessagePlaceholder()
    {
        return $this->getValue(self::XPATH_GIFT_MESSAGE_PLACEHOLDER);
    }

    public function isAddOptionsToEmail(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::XPATH_ADD_OPTIONS_TO_EMAIL, $storeId);
    }
}
