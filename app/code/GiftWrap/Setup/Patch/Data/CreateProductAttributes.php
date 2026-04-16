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
use Magento\Framework\Validator\ValidateException;

class CreateProductAttributes implements DataPatchInterface
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
     * @throws LocalizedException|ValidateException
     */
    public function apply(): self
    {
        if ($this->eavSetup->getAttribute(Product::ENTITY, self::AVAILABLE_FOR_WRAPPING)) {
            return $this;
        }

        $this->eavSetup->addAttribute(
            Product::ENTITY,
            self::AVAILABLE_FOR_WRAPPING,
            [
                'group' => 'Amasty Gift Wrap',
                'type' => 'int',
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Boolean::class,
                'frontend' => '',
                'label' => __('Available for Wrapping'),
                'input' => 'boolean',
                'class' => '',
                'source' => \Magento\Catalog\Model\Product\Attribute\Source\Boolean::class,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => 1,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'apply_to' => 'simple,configurable,bundle,grouped,giftcard,amgiftcard',
                'is_used_in_grid' => true,
                'note'
                => 'Current setting is active only if Amasty Gift Wrap plugin is Enabled in general configuration.'
            ]
        );

        $groupName = 'amasty-gift-wrap';
        $entityTypeId = $this->eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $this->eavSetup->getAttributeSetId($entityTypeId, 'Default');
        $attribute = $this->eavSetup->getAttribute($entityTypeId, self::AVAILABLE_FOR_WRAPPING);
        if ($attribute) {
            $this->eavSetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $groupName,
                $attribute['attribute_id'],
                61
            );
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
