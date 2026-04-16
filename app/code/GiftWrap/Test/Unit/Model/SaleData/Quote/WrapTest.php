<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\GiftWrap\Test\Unit\Model\SaleData\Quote;

use Amasty\GiftWrap\Api\SaleData\WrapInterface;
use Amasty\GiftWrap\Model\SaleData\Quote\Wrap;
use Amasty\GiftWrap\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\GiftWrap\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Wrap
 *
 * @see Wrap
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WrapTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers Wrap::addForItem
     * @dataProvider addForItemDataProvider
     */
    public function testAddForItem($quoteWrapId, $itemData, $wrapItemsData, $qty, $expectedQty)
    {
        /** @var Wrap $model */
        $model = $this->getObjectManager()->getObject(Wrap::class, ['data' => [WrapInterface::ID => $quoteWrapId]]);
        $quoteItem = $this->getObjectManager()->getObject(QuoteItem::class, ['data' => $itemData]);
        $wrapItems = [];
        foreach ($wrapItemsData as $wrapItemData) {
            $wrapItems[] = $this->getObjectManager()->getObject(DataObject::class, ['data' => $wrapItemData]);
        }
        $quoteItem->setWrapItems($wrapItems);
        $model->addForItem($quoteItem, $qty);
        $actualQty = 0;
        foreach ($quoteItem->getWrapItems() as $wrapItem) {
            if ($wrapItem->getAmGiftWrapQuoteItemId() == $quoteItem->getId()) {
                $actualQty = $wrapItem->getAmGiftWrapWrapQty();
            }
        }
        $this->assertEquals($expectedQty, $actualQty);
    }

    /**
     * Data provider for addForItem test
     * @return array
     */
    public function addForItemDataProvider()
    {
        return [
            [
                1,
                ['id' => 1],
                [],
                2,
                2
            ],
            [
                2,
                ['id' => 1],
                [
                    [
                        'am_gift_wrap_quote_wrap_id' => 2,
                        'am_gift_wrap_quote_item_id' => 1,
                        'am_gift_wrap_wrap_qty' => 1,
                        'is_modified' => true
                    ]
                ],
                2,
                3
            ],
            [
                2,
                ['id' => 1],
                [
                    [
                        'am_gift_wrap_quote_wrap_id' => 3,
                        'am_gift_wrap_quote_item_id' => 1,
                        'am_gift_wrap_wrap_qty' => 1,
                        'is_modified' => true
                    ]
                ],
                2,
                2
            ],
            [
                2,
                ['id' => 1, 'qty' => 10],
                [
                    [
                        'am_gift_wrap_quote_wrap_id' => 3,
                        'am_gift_wrap_quote_item_id' => 1,
                        'am_gift_wrap_wrap_qty' => 1,
                        'is_modified' => true
                    ]
                ],
                null,
                10
            ]
        ];
    }
}
