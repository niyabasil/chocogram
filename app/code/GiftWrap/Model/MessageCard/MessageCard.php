<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard;

use \Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Amasty\GiftWrap\Model\Wrapper\Wrap;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;

class MessageCard extends \Magento\Framework\Model\AbstractModel implements MessageCardInterface, IdentityInterface
{
    public const CACHE_TAG = 'amgiftwrap_message_card';

    public const PERSIST_NAME = 'amgiftwrap_message_card';

    /**
     * @var array
     */
    private $storeChanged = [];

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'amasty_gift_card';

    protected function _construct()
    {
        $this->_init(\Amasty\GiftWrap\Model\MessageCard\ResourceModel\MessageCard::class);
        $this->setIdFieldName(MessageCardInterface::ENTITY_ID);
    }

    /**
     * @return array
     */
    public function getStoreChanged()
    {
        return $this->storeChanged;
    }

    /**
     * @param array $data
     */
    public function addStoreData($data)
    {
        $data = array_filter($data, [$this, 'checkIfNotNull']);
        $this->storeChanged = array_keys($data);
        $this->addData($data);
    }

    /**
     * @param $var
     *
     * @return bool
     */
    public function checkIfNotNull($var)
    {
        return $var !== null;
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(MessageCardInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($messageCardId)
    {
        $this->setData(MessageCardInterface::ENTITY_ID, $messageCardId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(MessageCardInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(MessageCardInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(MessageCardInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(MessageCardInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(MessageCardInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(MessageCardInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->_getData(MessageCardInterface::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        $this->setData(MessageCardInterface::IMAGE, $image);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return (float) $this->_getData(MessageCardInterface::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        $this->setData(MessageCardInterface::PRICE, $price);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return $this->_getData(MessageCardInterface::SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setSortOrder($sortOrder)
    {
        $this->setData(MessageCardInterface::SORT_ORDER, $sortOrder);

        return $this;
    }
}
