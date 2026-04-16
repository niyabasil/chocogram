<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Adminhtml\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\GiftWrap\Model\Total
     */
    private $totalProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\GiftWrap\Model\Total $totalProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->totalProvider = $totalProvider;
    }

    /**
     * Initialize gift wrapping and printed card totals for order/invoice/creditmemo
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        if ($source->getWrapItems() || ($source->getOrder() && $source->getOrder()->getWrapItems())) {
            $totals = $this->totalProvider->getTotals($source);
            foreach ($totals as $total) {
                $this->getParentBlock()->addTotalBefore(new \Magento\Framework\DataObject($total), 'tax');
            }
        }

        return $this;
    }
}
