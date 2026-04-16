<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Ajax\Wrap;

use Amasty\GiftWrap\Controller\Ajax\AbstractAction;
use Amasty\GiftWrap\Model\SaleData\WrapManagement;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class Remove extends AbstractAction
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        Cart $cart,
        WrapManagement $wrapManagement,
        LayoutInterface $layout,
        Escaper $escaper,
        Context $context,
        CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct($cart, $wrapManagement, $layout, $escaper, $context);
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @inheritdoc
     */
    public function action()
    {
        $quoteWrapId = (int)$this->getRequest()->getParam('id');
        $this->getWrapManagement()->removeQuoteWrap($quoteWrapId);

        // need disable wrap saving after quote saving because wraps already updates by previous sql
        $this->getCart()->getQuote()->setIsWrapSaveDisabled(true);
        $items = $this->getCart()->getQuote()->getItems();

        foreach ($items as $item) {
            $wrapItems = $item->getWrapItems();
            foreach ($wrapItems as $wrapItem) {
                if ($wrapItem && (int)$wrapItem->getAmGiftWrapQuoteWrapId() === $quoteWrapId) {
                    $wrapItem->setIsDeleted(true);
                }
            }
        }

        $this->getCart()->getQuote()->collectTotals();
        $this->quoteRepository->save($this->getCart()->getQuote());

        return $this->getUpdatedBlocks(true);
    }
}
