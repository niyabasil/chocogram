<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Items as CreditmemoItems;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items as CreditmemoCreateItems;
use Magento\Sales\Block\Adminhtml\Order\Invoice\View\Items as InvoiceItems;
use Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Items as InvoiceCreateItems;
use Magento\Shipping\Block\Adminhtml\Create\Items as ShipmentCreateItems;
use Magento\Shipping\Block\Adminhtml\View\Items as ShipmentItems;

class ItemsPlugin
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param mixed $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml($subject, $result)
    {
        switch (true) {
            case $subject instanceof CreditmemoItems:
            case $subject instanceof CreditmemoCreateItems:
                $order = $this->registry->registry('current_creditmemo')->getOrder();
                break;
            case $subject instanceof InvoiceItems:
            case $subject instanceof InvoiceCreateItems:
                $order = $this->registry->registry('current_invoice')->getOrder();
                break;
            case $subject instanceof ShipmentItems:
            case $subject instanceof ShipmentCreateItems:
                $order = $this->registry->registry('current_shipment')->getOrder();
                break;
            default:
                $order = $this->registry->registry('current_order');
                break;
        }

        $wrappingBlock = $subject->getLayout()->createBlock(
            \Amasty\GiftWrap\Block\Adminhtml\Sales\Order\View\Info::class,
            'am_gift_wrap_view_info'
        )->setOrder($order);
        /** @var \Magento\Backend\Block\Template $giftBlock */
        $giftBlock = $subject->getLayout()->createBlock(
            \Magento\Backend\Block\Template::class,
            'gift_options',
            ['data' => ['template' => 'Magento_Sales::order/giftoptions.phtml']]
        );
        $giftBlock->setChild('am_gift_wrap_view_info', $wrappingBlock);

        return $giftBlock->toHtml() . $result;
    }
}
