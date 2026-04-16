<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Sales;

use Amasty\GiftWrap\Model\Total;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Totals extends Template
{
    /**
     * @var Total
     */
    private $totalProvider;

    public function __construct(
        Context $context,
        Total $totalProvider,
        array $data = []
    ) {
        $this->totalProvider = $totalProvider;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return \Amasty\GiftWrap\Block\Sales\Totals
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
