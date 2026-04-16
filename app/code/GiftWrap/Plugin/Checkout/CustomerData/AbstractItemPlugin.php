<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Checkout\CustomerData;

use Amasty\GiftWrap\Model\SaleData\ResourceModel\SaleData;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Quote\Model\Quote\Item;

class AbstractItemPlugin
{
    public const WRAP_ID = 'wrap_id';
    /**
     * @var SaleData
     */
    private $saleData;

    public function __construct(SaleData $saleData)
    {
        $this->saleData = $saleData;
    }

    /**
     * @param AbstractItem $subject
     * @param array $result
     * @param Item $item
     * @return array
     */
    public function afterGetItemData(AbstractItem $subject, array $result, Item $item)
    {
        $product = $item->getProduct();
        $availableForWrapping = (bool) $product->getData(
            CreateProductAttributes::AVAILABLE_FOR_WRAPPING
        );
        if ($product->getTypeId() == 'giftcard' && $product->getGiftcardType() != '1') {
            $availableForWrapping = false;
        }
        $result[CreateProductAttributes::AVAILABLE_FOR_WRAPPING] = $availableForWrapping;
        $result['has_free_qty'] = $this->saleData->isItemHasFreeQty($item);
        $result['qty_for_wrap'] = $this->saleData->getQtyForWrap($item);

        return $result;
    }
}
