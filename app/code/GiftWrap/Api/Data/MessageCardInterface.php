<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Api\Data;

interface MessageCardInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    public const MAIN_TABLE = 'amasty_giftwrap_message_card';
    public const STORE_TABLE = 'amasty_giftwrap_message_card_store';
    public const ENTITY_STORE_ID = 'entity_store_id';
    public const ENTITY_ID = 'entity_id';
    public const CREATED_AT = 'created_at';
    public const CARD_MESSAGE_ID = 'message_card_id';
    public const STORE_ID = 'store_id';
    public const NAME = 'name';
    public const STATUS = 'status';
    public const IMAGE = 'image';
    public const PRICE = 'price';
    public const SORT_ORDER = 'sort_order';

    public const UI_FIELDS = [
        self::NAME,
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
     * @param int $entityId
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function setName($name);

    /**
     * @return int|null
     */
    public function getStatus();

    /**
     * @param int|null $status
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getImage();

    /**
     * @param string|null $image
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function setImage($image);

    /**
     * @return float|null Message Card Price. Otherwise, null.
     */
    public function getPrice();

    /**
     * @param float|null $price
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function setPrice($price);

    /**
     * @return int|null
     */
    public function getSortOrder();

    /**
     * @param int|null $sortOrder
     *
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface
     */
    public function setSortOrder($sortOrder);
}
