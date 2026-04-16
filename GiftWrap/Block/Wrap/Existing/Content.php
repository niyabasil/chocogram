<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Wrap\Existing;

use Amasty\GiftWrap\Model\Price\Renderer as PriceRenderer;
use Amasty\GiftWrap\Model\Price\SalableFactory;
use Amasty\GiftWrap\Model\MessageCard\Resolver as CardResolver;
use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Model\Wrapper\Resolver as WrapResolver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template;

class Content extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_GiftWrap::components/existing_wrap/content.phtml';

    /**
     * @var array
     */
    private $existingWraps = null;

    /**
     * @var WrapResolver
     */
    private $wrapResolver;

    /**
     * @var CardResolver
     */
    private $cardResolver;

    /**
     * @var array|null
     */
    private $wrappers = null;

    /**
     * @var array|null
     */
    private $cards = null;

    /**
     * @var SaleData
     */
    private $saleData;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var PriceRenderer
     */
    private $priceRenderer;

    /**
     * @var SalableFactory
     */
    private $salableFactory;

    public function __construct(
        CheckoutSession $checkoutSession,
        SaleData $saleData,
        WrapResolver $wrapResolver,
        CardResolver $cardResolver,
        PriceRenderer $priceRenderer,
        SalableFactory $salableFactory,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wrapResolver = $wrapResolver;
        $this->saleData = $saleData;
        $this->checkoutSession = $checkoutSession;
        $this->cardResolver = $cardResolver;
        $this->priceRenderer = $priceRenderer;
        $this->salableFactory = $salableFactory;
    }

    /**
     * @return SaleData
     */
    public function getSaleDataModel()
    {
        return $this->saleData;
    }

    /**
     * @return CheckoutSession
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return array|null
     */
    public function getWrappers()
    {
        if ($this->wrappers === null) {
            $this->wrappers = $this->wrapResolver->getAssoc(
                $this->_storeManager->getStore()->getId(),
                array_column($this->getExistingWraps(), 'wrap_id')
            );
        }

        return $this->wrappers;
    }

    /**
     * @return array|null
     */
    public function getCards()
    {
        if ($this->cards === null) {
            $this->cards = $this->cardResolver->getAssoc(
                $this->_storeManager->getStore()->getId(),
                array_column($this->getExistingWraps(), 'card_id')
            );
        }

        return $this->cards;
    }

    /**
     * @return array
     */
    public function getExistingWraps()
    {
        if ($this->existingWraps === null) {
            $this->existingWraps = $this->getSaleDataModel()->loadWrapsByQuoteId(
                $this->getCheckoutSession()->getQuoteId()
            );
        }

        return $this->existingWraps;
    }

    /**
     * @param int $wrapId
     * @return string
     */
    public function getWrapName($wrapId)
    {
        $wrappers = $this->getWrappers();
        return isset($wrappers[$wrapId]) ? $wrappers[$wrapId]->getName() : '';
    }

    /**
     * @param int $cardId
     * @return string
     */
    public function getCardName($cardId)
    {
        $cards = $this->getCards();
        return isset($cards[$cardId]) ? $cards[$cardId]->getName() : '';
    }

    public function getPackPriceHtml(int $wrapId, int $cardId): string
    {
        $salableItems = [];

        $wrappers = $this->getWrappers();
        $wrap = $wrappers[$wrapId] ?? null;
        if ($wrap) {
            $salableItems[] = $this->salableFactory->create($wrap);
        }

        $cards = $this->getCards();
        $card = $cards[$cardId] ?? null;
        if ($card) {
            $salableItems[] = $this->salableFactory->create($card);
        }

        return $this->priceRenderer->execute($salableItems);
    }
}
