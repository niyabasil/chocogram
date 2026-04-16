<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\GiftWrap\Test\Unit\Block\Adminhtml\Sales\Order\View;

use Amasty\GiftWrap\Block\Adminhtml\Sales\Order\View\Info;
use Amasty\GiftWrap\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\GiftWrap\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;

/**
 * Class Info
 *
 * @see Info
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers Info::getColumnHtml
     * @dataProvider getColumnHtmlDataProvider
     */
    public function testGetColumnHtml($itemData, $columnName, $isObject, $expected)
    {
        /** @var Info $block */
        $block = $this->createPartialMock(Info::class, ['getWrapUrl', 'getCardUrl']);
        $this->setProperty(
            $block,
            '_escaper' ,
            $this->getObjectManager()->getObject(Escaper::class),
            Info::class
        );
        $block->expects($this->any())->method('getWrapUrl')->willReturnCallback(
            function ($id) {
                return 'amgiftwrap/wrap/edit/' . $id;
            }
        );
        $block->expects($this->any())->method('getCardUrl')->willReturnCallback(
            function ($id) {
                return 'amgiftwrap/message_card/edit/' . $id;
            }
        );
        if ($isObject) {
            $itemData = $this->getObjectManager()->getObject(DataObject::class, ['data' => $itemData]);
        }
        $this->assertEquals($expected, $block->getColumnHtml($itemData, $columnName));
    }

    /**
     * Data provider for getColumnHtml test
     * @return array
     */
    public function getColumnHtmlDataProvider()
    {
        return [
            [
                ['wrap_id' => 3, 'wrap' => 'test'],
                'wrap',
                false,
                '<a href="amgiftwrap/wrap/edit/3" target="_blank">test</a>'
            ],
            [
                ['card_id' => 3, 'card' => 'test'],
                'card',
                false,
                '<a href="amgiftwrap/message_card/edit/3" target="_blank">test</a>'
            ],
            [
                ['gift_message' => "xxxx\nyyyy\nzzzz"],
                'gift_message',
                false,
                "xxxx<br />\nyyyy<br />\nzzzz"
            ],
            [
                ['wrap_id' => 3, 'wrap' => 'test'],
                'wrap',
                true,
                '<a href="amgiftwrap/wrap/edit/3" target="_blank">test</a>'
            ],
            [
                ['card_id' => 3, 'card' => 'test'],
                'card',
                true,
                '<a href="amgiftwrap/message_card/edit/3" target="_blank">test</a>'
            ],
            [
                ['gift_message' => "xxxx\nyyyy\nzzzz"],
                'gift_message',
                true,
                "xxxx<br />\nyyyy<br />\nzzzz"
            ]
        ];
    }
}
