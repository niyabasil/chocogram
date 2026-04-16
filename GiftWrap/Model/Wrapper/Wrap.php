<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Wrapper;

use \Amasty\GiftWrap\Api\Data\WrapInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Wrap extends \Magento\Framework\Model\AbstractModel implements WrapInterface, IdentityInterface
{
    public const CACHE_TAG = 'amgiftwrap_wrap';

    public const PERSIST_NAME = 'amgiftwrap_wrap';

    /**
     * @var array
     */
    private $storeChanged = [];

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'amasty_gift_wrap';

    protected function _construct()
    {
        $this->_init(\Amasty\GiftWrap\Model\Wrapper\ResourceModel\Wrap::class);
        $this->setIdFieldName(WrapInterface::ENTITY_ID);
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
        return $this->_getData(WrapInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($wrapId)
    {
        $this->setData(WrapInterface::ENTITY_ID, $wrapId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(WrapInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(WrapInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(WrapInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(WrapInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_getData(WrapInterface::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->setData(WrapInterface::DESCRIPTION, $description);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(WrapInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(WrapInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->_getData(WrapInterface::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        $this->setData(WrapInterface::IMAGE, $image);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return (float)$this->_getData(WrapInterface::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        $this->setData(WrapInterface::PRICE, $price);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return $this->_getData(WrapInterface::SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setSortOrder($sortOrder)
    {
        $this->setData(WrapInterface::SORT_ORDER, $sortOrder);

        return $this;
    }
}
