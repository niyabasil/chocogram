<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Api\Data;

use Amasty\GiftWrap\Model\Wrapper\Wrap;

interface WrapInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const MAIN_TABLE = 'amasty_giftwrap_wrap';
    public const STORE_TABLE = 'amasty_giftwrap_wrap_store';
    public const QUOTE_CARD_ITEM_TABLE = 'amasty_giftwrap_quote_card_item';
    public const ORDER_WRAP_ITEM_TABLE = 'amasty_giftwrap_order_wrap_item';
    public const ORDER_CARD_ITEM_TABLE = 'amasty_giftwrap_order_card_item';
    public const ENTITY_STORE_ID = 'entity_store_id';
    public const ENTITY_ID = 'entity_id';
    public const CREATED_AT = 'created_at';
    public const WRAP_ID = 'wrap_id';
    public const STORE_ID = 'store_id';
    public const NAME = 'name';
    public const DESCRIPTION = 'description';
    public const STATUS = 'status';
    public const IMAGE = 'image';
    public const PRICE = 'price';
    public const SORT_ORDER = 'sort_order';

    public const UI_FIELDS = [
        self::NAME,
        self::DESCRIPTION,
        self::STATUS,
        self::IMAGE,
        self::PRICE,
        self::SORT_ORDER
    ];
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $wrapId
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setEntityId($wrapId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setName($name);

    /**
     * @return string|null Wrap Description. Otherwise, null.
     */
    public function getDescription();

    /**
     * @param string|null $description
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setDescription($description);

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @param int|null $status
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getImage();

    /**
     * @param string|null $image
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setImage($image);

    /**
     * @return float|null Wrap Price. Otherwise, null.
     */
    public function getPrice();

    /**
     * @param float|null $price
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setPrice($price);

    /**
     * @return int|null
     */
    public function getSortOrder();

    /**
     * @param int|null $sortOrder
     *
     * @return \Amasty\GiftWrap\Api\Data\WrapInterface
     */
    public function setSortOrder($sortOrder);
}
