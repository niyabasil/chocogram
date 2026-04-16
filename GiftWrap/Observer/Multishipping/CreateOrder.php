<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer\Multishipping;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order;

class CreateOrder implements ObserverInterface
{
    /**
     * @var array
     */
    private $ignoreFields = [
        'am_gift_wrap_wrap_ids_count',
        'am_gift_wrap_quote_address_id'
    ];

    /**
     * @var \Amasty\GiftWrap\Api\WrapRepositoryInterface
     */
    private $wrapRepository;

    /**
     * @var \Amasty\GiftWrap\Api\MessageCardRepositoryInterface
     */
    private $cardRepository;

    public function __construct(
        \Amasty\GiftWrap\Api\WrapRepositoryInterface $wrapRepository,
        \Amasty\GiftWrap\Api\MessageCardRepositoryInterface $cardRepository
    ) {
        $this->wrapRepository = $wrapRepository;
        $this->cardRepository = $cardRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        /** @var Address $address */
        $address = $observer->getAddress();

        foreach ($address->getData() as $key => $value) {
            if (strpos($key, 'am_gift_wrap_') === 0 && !in_array($key, $this->ignoreFields)) {
                $order->setData($key, $value);
            }
        }
        
        $wrapItems = [];
        foreach ($address->getWrapItems() as $wrapItem) {
            $wrapData = $wrapItem->getData();
            unset($wrapData['am_gift_wrap_quote_address_id']);

            try {
                $wrap = $this->wrapRepository->getById(
                    $wrapData['am_gift_wrap_wrap_id'] ?? 0,
                    $address->getQuote()->getStore()->getId()
                );
                $wrapData['am_gift_wrap_wrap_name'] = $wrap->getName();

                if (isset($wrapData['am_gift_wrap_card_id'])) {
                    $card = $this->cardRepository->getById(
                        $wrapData['am_gift_wrap_card_id'] ?? 0,
                        $address->getQuote()->getStore()->getId()
                    );
                    $wrapData['am_gift_wrap_card_name'] = $card->getName();
                }
            } catch (NoSuchEntityException $e) {
                continue;
            }

            $wrapItems[] = new DataObject($wrapData);
        }

        $order->setWrapItems($wrapItems);
    }
}
