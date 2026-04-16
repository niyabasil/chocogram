<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Ui\DataProvider\Modifier;

use Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Amasty\GiftWrap\Api\Data\WrapInterface;
use Amasty\GiftWrap\Model\MessageCard\MessageCard;
use Amasty\GiftWrap\Model\Wrapper\Wrap;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;

class UseDefault implements ModifierInterface
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry,
        RequestInterface $request
    ) {
        $this->storeId = (int)$request->getParam('store', 0);
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        if ($this->isShowDefaultCheckbox()) {
            $entity = $this->getEntity();
            $changedFields = $entity->getStoreChanged();
            $uiFields = $entity instanceof Wrap ? WrapInterface::UI_FIELDS : MessageCardInterface::UI_FIELDS;
            foreach ($uiFields as $field) {
                $meta['general']['children'][$field]['arguments']['data']['config']['service'] =
                    [
                        'template' => 'ui/form/element/helper/service'
                    ];

                if (!in_array($field, $changedFields)) {
                    $meta['general']['children'][$field]['arguments']['data']['config']['disabled'] = true;
                }
            }
        }

        return $meta;
    }

    /**
     * @return Wrap|MessageCard
     */
    protected function getEntity()
    {
        $entity = $this->registry->registry(Wrap::PERSIST_NAME);
        if (!$entity) {
            $entity = $this->registry->registry(MessageCard::PERSIST_NAME);
        }

        return $entity;
    }

    /**
     * @return bool
     */
    private function isShowDefaultCheckbox()
    {
        return (bool)$this->storeId;
    }
}
