<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Multishipping\Block\Checkout;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\OptionSource\Allow;
use Amasty\GiftWrap\Model\SaleData\WrapManagement;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Framework\View\LayoutInterface;
use Magento\Multishipping\Block\Checkout\Shipping;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class ShippingPlugin
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var WrapManagement
     */
    private $wrapManagement;

    /**
     * @var array
     */
    private $disabledForWrapProducts = [];

    /**
     * @var array
     */
    private $disabledForWrapProductIds = [];

    public function __construct(
        ConfigProvider $configProvider,
        LayoutInterface $layout,
        WrapManagement $wrapManagement
    ) {
        $this->layout = $layout;
        $this->configProvider = $configProvider;
        $this->wrapManagement = $wrapManagement;
    }

    /**
     * @param Shipping $subject
     * @param string $result
     * @param DataObject $addressEntity
     * @return string
     */
    public function afterGetItemsBoxTextAfter(Shipping $subject, string $result, DataObject $addressEntity)
    {
        /** @var \Amasty\GiftWrap\Block\Product\View $buttonBlock */
        if ($this->configProvider->isEnabledOnPage(Allow::CHECKOUT_PAGE)
            && $this->validateItems($addressEntity)
            && ($buttonBlock = $this->layout->getBlock('amgiftwrap.multishipping'))
        ) {
            /** @var \Amasty\GiftWrap\Block\Product\View\Messages $messagesBlock */
            $messagesBlock = $buttonBlock->getChildBlock('messages');
            if ($messagesBlock && $this->disabledForWrapProducts) {
                $messagesBlock->addError($this->getErrorMessage());
                $this->disabledForWrapProducts = [];
                $this->disabledForWrapProductIds = [];
            }
            $wrapItems = $addressEntity->getWrapItems() ?: [];
            if ($wrapItems) {
                $buttonBlock->setSelectedWrap(reset($wrapItems));
            }
            $result .= str_replace(
                'amwrap[',
                sprintf('amwrap[%s][', $addressEntity->getId()),
                $buttonBlock->toHtml()
            );
            $buttonBlock->setSelectedWrap(null);
        }

        return $result;
    }

    /**
     * @param Address|DataObject $addressEntity
     * @return bool
     */
    private function validateItems(DataObject $addressEntity)
    {
        $result = false;
        /** @var AbstractItem $item */
        foreach ($addressEntity->getAllVisibleItems() as $item) {
            if ($this->wrapManagement->isProductCanWrapped($item->getProduct())) {
                $result = true;
            } elseif (!in_array($item->getProduct()->getId(), $this->disabledForWrapProductIds)) {
                $this->disabledForWrapProducts[] = $item->getProduct()->getName();
                $this->disabledForWrapProductIds[] = $item->getProduct()->getId();
            }
        }

        return $result;
    }

    /**
     * @return Phrase
     */
    private function getErrorMessage()
    {
        $products = implode(', ', $this->disabledForWrapProducts);
        switch (count($this->disabledForWrapProducts)) {
            case 1:
                $message = __(
                    'Kindly note: %1 is not available for wrapping and therefore won\'t be wrapped.',
                    $products
                );
                break;
            default:
                $message = __(
                    'Kindly note: %1 are not available for wrapping and therefore won\'t be wrapped.',
                    $products
                );
                break;
        }

        return $message;
    }
}
