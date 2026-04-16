<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateAttributeAmGiftCardCompatibility implements DataPatchInterface
{
    public const AVAILABLE_FOR_WRAPPING = 'am_available_for_wrapping';

    /**
     * @var EavSetup
     */
    private $eavSetup;

    public function __construct(
        EavSetup $eavSetup
    ) {
        $this->eavSetup = $eavSetup;
    }

    /**
     * @throws LocalizedException
     */
    public function apply(): self
    {
        if (!($attribute = $this->eavSetup->getAttribute(Product::ENTITY, self::AVAILABLE_FOR_WRAPPING))) {
            return $this;
        }

        $this->eavSetup->updateAttribute(
            Product::ENTITY,
            $attribute['attribute_id'],
            'apply_to',
            'simple,configurable,bundle,grouped,giftcard,amgiftcard'
        );

        return $this;
    }

    public static function getDependencies()
    {
        return [
            CreateProductAttributes::class
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
