<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Ajax;

use Amasty\GiftWrap\Block\Checkout\Cart\Wrap\ListWrap;
use Amasty\GiftWrap\Block\Checkout\Cart\Wrap\Renderer as CartButtonRenderer;
use Amasty\GiftWrap\Block\Checkout\Item\Wrap\Renderer as ItemButtonRenderer;
use Amasty\GiftWrap\Block\Wrap\Existing\Content;
use Laminas\Http\AbstractMessage;
use Laminas\Http\Response;
use Amasty\GiftWrap\Model\SaleData\WrapManagement;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\LayoutInterface;

abstract class AbstractAction extends Action
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var WrapManagement
     */
    private $wrapManagement;

    /**
     * @var LayoutInterface
     */
    private $layout;
    /**
     * @var Cart
     */
    private $cart;

    public function __construct(
        Cart $cart,
        WrapManagement $wrapManagement,
        LayoutInterface $layout,
        Escaper $escaper,
        Context $context
    ) {
        parent::__construct($context);
        $this->escaper = $escaper;
        $this->wrapManagement = $wrapManagement->setType(WrapManagement::QUOTE_TYPE);
        $this->layout = $layout;
        $this->cart = $cart;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            if ($this->getRequest()->isXmlHttpRequest()) {
                $data = $this->action();
            } else {
                $resultJson->setStatusHeader(
                    Response::STATUS_CODE_403,
                    AbstractMessage::VERSION_11,
                    'Forbidden'
                );
                $data = [
                    'error' => $this->escaper->escapeHtml('Forbidden'),
                    'errorcode' => 403
                ];
            }
        } catch (LocalizedException $e) {
            $resultJson->setStatusHeader(
                Response::STATUS_CODE_400,
                AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $data = [
                'error' => $this->escaper->escapeHtml($e->getMessage()),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
        } catch (\Exception $e) {
            $resultJson->setStatusHeader(
                Response::STATUS_CODE_400,
                AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $data = [
                'error' => $this->escaper->escapeHtml(__('System exception')),
                'errorcode' => $this->escaper->escapeHtml($e->getCode())
            ];
        }

        return $resultJson->setData($data);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    abstract public function action();

    /**
     * @return WrapManagement
     */
    public function getWrapManagement()
    {
        return $this->wrapManagement;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param bool $reloadInfo
     * @return array
     */
    public function getUpdatedBlocks($reloadInfo = false)
    {
        $data = [];

        $listBlock = $this->layout->createBlock(ListWrap::class);
        $existingBlock = $this->layout->createBlock(Content::class);
        /** @var ItemButtonRenderer $itemBlock */
        $itemBlock = $this->layout->createBlock(ItemButtonRenderer::class);

        if ($reloadInfo) {
            // need reload wrap items for getting latest update info
            $this->getCart()->getQuote()->setIsAdditionalDataLoaded(false);
        }
        foreach ($this->getCart()->getQuote()->getItemsCollection() as $quoteItem) {
            $data['item_button_' . $quoteItem->getId()] = $itemBlock->setItem($quoteItem)->toHtml();
        }
        $data['list_block'] = $listBlock->toHtml();
        $data['existing_block'] = $existingBlock->toHtml();

        return $data;
    }
}
