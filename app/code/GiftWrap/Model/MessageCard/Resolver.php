<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\MessageCard;

use Amasty\GiftWrap\Api\Data\MessageCardInterface;
use Amasty\GiftWrap\Model\MessageCard\ResourceModel\Collection as MessageCardCollection;
use Amasty\GiftWrap\Model\MessageCard\ResourceModel\CollectionFactory as MessageCardCollectionFactory;

class Resolver
{
    /**
     * @var MessageCardCollectionFactory
     */
    private $messageCardCollectionFactory;

    public function __construct(MessageCardCollectionFactory $messageCardCollectionFactory)
    {
        $this->messageCardCollectionFactory = $messageCardCollectionFactory;
    }

    /**
     * @param int $storeId
     * @param null|array $cardIds
     * @return array
     */
    public function getSorted($storeId, $cardIds = null)
    {
        $messageCards = [];
        $defaultCollection = $this->getMessageCardCollectionByStore($cardIds);
        $storeCollection = $this->getMessageCardCollectionByStore($cardIds, $storeId);
        foreach ($defaultCollection as $card) {
            /** @var MessageCard $card */
            $storeModel = $storeCollection->getItemById($card->getEntityId());
            if ($storeModel) {
                $card->addStoreData($storeModel->getData());
            }

            if ($card->getStatus()) {
                $sortOrder = (int)$card->getSortOrder();
                while (true) {
                    if (!isset($messageCards[$sortOrder])) {
                        break;
                    }
                    $sortOrder++;
                }
                $messageCards[$sortOrder] = $card;
            }
        }
        ksort($messageCards);

        return $messageCards;
    }

    /**
     * @param int $storeId
     * @param array|null $cardIds
     * @return array
     */
    public function getAssoc($storeId, $cardIds = null)
    {
        $messageCards = [];
        $defaultCollection = $this->getMessageCardCollectionByStore($cardIds);
        $storeCollection = $this->getMessageCardCollectionByStore($cardIds, $storeId);
        foreach ($defaultCollection as $messageCard) {
            /** @var MessageCard $messageCard */
            $storeModel = $storeCollection->getItemById($messageCard->getEntityId());
            if ($storeModel) {
                $messageCard->addStoreData($storeModel->getData());
            }

            if ($messageCard->getStatus()) {
                $messageCards[$messageCard->getId()] = $messageCard;
            }
        }

        return $messageCards;
    }

    /**
     * @param int $store
     *
     * @return MessageCardCollection
     */
    private function getMessageCardCollectionByStore($cardIds, $store = 0)
    {
        /** @var MessageCardCollection $wrappers */
        $messageCards = $this->messageCardCollectionFactory->create();
        $messageCards->joinStoreTable($store);
        if ($cardIds) {
            $messageCards->addFieldToFilter(MessageCardInterface::ENTITY_ID, $cardIds);
        }

        return $messageCards;
    }
}
