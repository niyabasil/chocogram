<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Setup;

use Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Amasty\GiftWrap\Api\Data\WrapInterface;
use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface as ModuleDataSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Catalog\Model\Product;

class Uninstall implements UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(EavSetupFactory $eavSetupFactory, ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tablesToDrop = [
            MessageCardInterface::STORE_TABLE,
            MessageCardInterface::MAIN_TABLE,
            WrapInterface::MAIN_TABLE,
            WrapInterface::STORE_TABLE,
            SaleDataResourceInterface::QUOTE_TABLE,
            SaleDataResourceInterface::QUOTE_WRAP_TABLE,
            SaleDataResourceInterface::QUOTE_ITEM_TABLE,
            SaleDataResourceInterface::QUOTE_ADDRESS_TABLE,
            SaleDataResourceInterface::QUOTE_ADDRESS_ITEM_TABLE,
            SaleDataResourceInterface::ORDER_TABLE,
            SaleDataResourceInterface::ORDER_WRAP_TABLE,
            SaleDataResourceInterface::ORDER_ITEM_TABLE,
            SaleDataResourceInterface::INVOICE_TABLE,
            SaleDataResourceInterface::CREDITMEMO_TABLE
        ];

        foreach ($tablesToDrop as $table) {
            $setup->getConnection()->dropTable(
                $setup->getTable($table)
            );
        }

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Product::ENTITY, CreateProductAttributes::AVAILABLE_FOR_WRAPPING);
        $attributeSetIds = $eavSetup->getAllAttributeSetIds(Product::ENTITY);
        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->removeAttributeGroup(
                Product::ENTITY,
                $attributeSetId,
                'amasty-gift-wrap'
            );
        }

        $setup->endSetup();
    }
}
