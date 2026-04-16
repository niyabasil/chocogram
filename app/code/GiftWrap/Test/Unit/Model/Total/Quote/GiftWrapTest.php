<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\GiftWrap\Test\Unit\Model\Total\Quote;

use Amasty\GiftWrap\Model\Total\Quote\GiftWrap;
use Amasty\GiftWrap\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\GiftWrap\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;

/**
 * Class GiftWrap
 *
 * @see GiftWrap
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftWrapTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers GiftWrap::collect
     * @dataProvider collectDataProvider
     */
    public function testCollect($quoteItemsData, $quoteWrapsData, $addressType, $expectedTotal)
    {
        /** @var GiftWrap $model */
        $model = $this->getObjectManager()->getObject(GiftWrap::class);
        $shippingAssignment = $this->createMock(\Magento\Quote\Model\ShippingAssignment::class);
        $total = $this->getObjectManager()->getObject(\Magento\Quote\Model\Quote\Address\Total::class);
        $priceCurrency = $this->createMock(\Magento\Directory\Model\PriceCurrency::class);

        $priceCurrency->expects($this->any())->method('convert')->willReturnArgument(0);
        $this->setProperty($model, 'priceCurrency', $priceCurrency, GiftWrap::class);
        $quoteWraps = [];
        foreach ($quoteWrapsData as $quoteWrapData) {
            $quoteWraps[$quoteWrapData['am_gift_wrap_entity_id']] = $this->getObjectManager()->getObject(
                DataObject::class,
                ['data' => $quoteWrapData]
            );
        }
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class,
            ['getStore']
        );
        $quote->expects($this->any())->method('getStore')->willReturn(false);
        $quote->setData('is_multi_shipping', false);
        $quote->setData('wrap_items', $quoteWraps);
        $address = $this->getObjectManager()->getObject(DataObject::class, ['data' => [
            'address_type' => $addressType,
            'quote' => $this->getObjectManager()->getObject(DataObject::class, ['data' => [
                'wrap_items' => $quoteWraps
            ]])
        ]]);
        $shippingAssignment->expects($this->any())->method('getShipping')->willReturn(
            $this->getObjectManager()->getObject(DataObject::class, ['data' => ['address' => $address]])
        );
        $quoteItems = [];
        foreach ($quoteItemsData as $quoteItemData) {
            foreach ($quoteItemData as $key => $data) {
                switch ($key) {
                    case 'product':
                        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
                        $quoteItemData[$key] = $product;
                        $product->expects($this->any())->method('isVirtual')->willReturn($data['is_virtual']);
                        break;
                    case 'wrap_items':
                        $wrapItems = [];
                        foreach ($data as $wrapItemData) {
                            $wrapItems[] = $this->getObjectManager()->getObject(DataObject::class,
                                ['data' => $wrapItemData]
                            );
                        }
                        $quoteItemData[$key] = $wrapItems;
                        break;
                }
            }
            $quoteItems[] = $this->getObjectManager()->getObject(DataObject::class,
                ['data' => $quoteItemData]
            );
        }
        $shippingAssignment->expects($this->any())->method('getItems')->willReturn($quoteItems);
        $model->collect($quote, $shippingAssignment, $total);
        $this->assertEquals($expectedTotal, $quote->getAmGiftWrapTotalPrice());
        $this->assertEquals($expectedTotal, $total->getGrandTotal());
    }

    /**
     * Data provider for collect test
     * @return array
     */
    public function collectDataProvider()
    {
        return [
            [
                [
                    [
                        'product' => [
                            'is_virtual' => false
                        ],
                        'wrap_items' => [
                            [
                                'am_gift_wrap_quote_wrap_id' => 1
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'am_gift_wrap_entity_id' => 1,
                        'am_gift_wrap_base_price' => 10
                    ]
                ],
                Address::TYPE_SHIPPING,
                10
            ],
            [
                [
                    [
                        'product' => [
                            'is_virtual' => true
                        ],
                        'wrap_items' => [
                            [
                                'am_gift_wrap_quote_wrap_id' => 1
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'am_gift_wrap_entity_id' => 1,
                        'am_gift_wrap_base_price' => 10
                    ]
                ],
                Address::TYPE_SHIPPING,
                0
            ],
            [
                [
                    [
                        'product' => [
                            'is_virtual' => false
                        ],
                        'wrap_items' => [
                            [
                                'am_gift_wrap_quote_wrap_id' => 1
                            ],
                            [
                                'am_gift_wrap_quote_wrap_id' => 2
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'am_gift_wrap_entity_id' => 1,
                        'am_gift_wrap_base_price' => 10
                    ],
                    [
                        'am_gift_wrap_entity_id' => 2,
                        'am_gift_wrap_base_price' => 11
                    ]
                ],
                Address::TYPE_SHIPPING,
                21
            ],
            [
                [
                    [
                        'product' => [
                            'is_virtual' => false
                        ],
                        'wrap_items' => [
                            [
                                'am_gift_wrap_quote_wrap_id' => 1
                            ],
                            [
                                'am_gift_wrap_quote_wrap_id' => 2
                            ]
                        ]
                    ],
                    [
                        'product' => [
                            'is_virtual' => false
                        ],
                        'wrap_items' => [
                            [
                                'am_gift_wrap_quote_wrap_id' => 2
                            ],
                            [
                                'am_gift_wrap_quote_wrap_id' => 3
                            ],
                            [
                                'am_gift_wrap_quote_wrap_id' => 13
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'am_gift_wrap_entity_id' => 1,
                        'am_gift_wrap_base_price' => 10
                    ],
                    [
                        'am_gift_wrap_entity_id' => 2,
                        'am_gift_wrap_base_price' => 11
                    ],
                    [
                        'am_gift_wrap_entity_id' => 3,
                        'am_gift_wrap_base_price' => 12
                    ]
                ],
                Address::TYPE_SHIPPING,
                33
            ]
        ];
    }
}
