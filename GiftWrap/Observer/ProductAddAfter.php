<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Observer;

use Amasty\GiftWrap\Model\Di\Wrapper;
use Amasty\GiftWrap\Model\SaleData\WrapManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Psr\Log\LoggerInterface;

class ProductAddAfter implements ObserverInterface
{
    /**
     * @var WrapManagement
     */
    private $wrapManagement;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Wrapper
     */
    private $promoItemRegistryGiftWrap;

    public function __construct(
        WrapManagement $wrapManagement,
        ManagerInterface $messageManager,
        RequestInterface $request,
        LoggerInterface $logger,
        Wrapper $promoItemRegistryGiftWrap
    ) {
        $this->wrapManagement = $wrapManagement->setType(WrapManagement::QUOTE_TYPE);
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->request = $request;
        $this->promoItemRegistryGiftWrap = $promoItemRegistryGiftWrap;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer): void
    {
        $items = $observer->getEvent()->getItems();
        $wrapData = $this->request->getParam('amwrap', []);
        $related = $this->request->getParam('related_product', []);
        if (!empty($related)) {
            $related = explode(',', $related);
        }
        if (!is_array($related)) {
            $related = [];
        }

        $amRelated = $this->request->getParam('amrelated_products', []);
        if (is_array($amRelated) && !empty($amRelated)) {
            $amRelated = array_keys($amRelated);
            $related = array_merge($amRelated, $related);
        }

        if (!empty($wrapData)
            && isset($wrapData[WrapManagement::FINISH_KEY])
            && $wrapData[WrapManagement::FINISH_KEY] == '1'
            && !$this->isPromoProducts($items)
        ) {
            /** @var QuoteItem $item */
            foreach ($items as $item) {
                try {
                    if (!$item->isDeleted()
                        && !$item->getParentItemId()
                        && !$item->getParentItem()
                        && !in_array($item->getProduct()->getId(), $related)
                    ) {
                        $quoteWrap = $this->wrapManagement->wrapQuoteItem(
                            $item,
                            $item->getQtyToAdd() ?: null,
                            $wrapData
                        );
                        $wrapData['existing_wrap_id'] = $quoteWrap->getId();
                    }
                } catch (NoSuchEntityException $exception) {
                    $this->messageManager->addErrorMessage(__(
                        'Can\'t add product %1 for this wrap',
                        $item->getProduct()->getId()
                    ));
                    $this->logger->error($exception->getMessage());
                }
            }
        }
    }

    private function isPromoProducts(array $items): bool
    {
        $itemsForAutoAdd = $this->promoItemRegistryGiftWrap->getItemsForAutoAdd() ?: [];

        return $itemsForAutoAdd && !empty(array_intersect_key($items, $itemsForAutoAdd));
    }
}
