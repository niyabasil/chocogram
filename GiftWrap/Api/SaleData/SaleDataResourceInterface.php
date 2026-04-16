<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Api\SaleData;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @api
 */
interface SaleDataResourceInterface
{
    public const QUOTE_TABLE = 'amasty_giftwrap_quote';
    public const QUOTE_WRAP_TABLE = 'amasty_giftwrap_quote_wrap';
    public const QUOTE_ITEM_TABLE = 'amasty_giftwrap_quote_item';
    public const QUOTE_ADDRESS_TABLE = 'amasty_giftwrap_quote_address';
    public const QUOTE_ADDRESS_ITEM_TABLE = 'amasty_giftwrap_quote_address_item';
    public const QUOTE_ADDRESS_WRAP_TABLE = 'amasty_giftwrap_quote_address_wrap';
    public const ORDER_TABLE = 'amasty_giftwrap_order';
    public const ORDER_WRAP_TABLE = 'amasty_giftwrap_order_wrap';
    public const ORDER_ITEM_TABLE = 'amasty_giftwrap_order_item';
    public const INVOICE_TABLE = 'amasty_giftwrap_invoice';
    public const CREDITMEMO_TABLE = 'amasty_giftwrap_creditmemo';

    /**
     *
     * Load gift wrap data for entity
     *
     * @param string $tableName
     * @param string $fieldName
     * @param DataObject $entity
     * @return DataObject
     */
    public function loadData($tableName, $fieldName, $entity);

    /**
     *
     * Save gift wrap data for entity
     *
     * @param string $tableName
     * @param string $fieldName
     * @param DataObject $entity
     * @return SaleDataResourceInterface
     */
    public function saveData($tableName, $fieldName, $entity);

    /**
     * @param string $tableName
     * @param string $fieldName
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    public function loadCollectionData($tableName, $fieldName, AbstractCollection $collection);
}
