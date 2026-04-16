<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\GiftWrap\Test\Unit\Block\Checkout\Cart\Wrap;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\GiftWrap\Block\Checkout\Cart\Wrap\Renderer;
use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Amasty\GiftWrap\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\GiftWrap\Test\Unit\Traits\ReflectionTrait;
use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Renderer
 *
 * @see Renderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RendererTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers Renderer::isAddButtonActive
     * @dataProvider isAddButtonActiveDataProvider
     */
    public function testIsAddButtonActive($enabledOnPage, $quoteItemsData, $expectedResult)
    {
        if (!class_exists(ConfigProviderAbstract::class)) {
            $this->getMockBuilder(ConfigProviderAbstract::class)->getMock();
        }

        /** @var Renderer $block */
        $block = $this->getObjectManager()->getObject(Renderer::class);
        $configProvider = $this->createMock(ConfigProvider::class);
        $checkoutSession = $this->createMock(Session::class);
        $quote = $this->createMock(Quote::class);
        $quoteItems = [];
        foreach ($quoteItemsData as $quoteItemData) {
            $quoteItem = $this->createMock(QuoteItem::class);
            $quoteItem->expects($this->any())->method('getProduct')->willReturn(new DataObject($quoteItemData));
            $quoteItems[] = $quoteItem;
        }
        $configProvider->expects($this->any())->method('isEnabledOnPage')->willReturn($enabledOnPage);
        $quote->expects($this->any())->method('getAllVisibleItems')->willReturn($quoteItems);
        $checkoutSession->expects($this->any())->method('getQuote')->willReturn($quote);
        $this->setProperty($block, 'configProvider', $configProvider, Renderer::class);
        $this->setProperty($block, 'checkoutSession', $checkoutSession, Renderer::class);
        $this->assertEquals($expectedResult, $block->isAddButtonActive());
    }

    /**
     * Data provider for isAddButtonActive test
     * @return array
     */
    public function isAddButtonActiveDataProvider()
    {
        return [
            [
                true,
                [
                    [CreateProductAttributes::AVAILABLE_FOR_WRAPPING => false],
                    [CreateProductAttributes::AVAILABLE_FOR_WRAPPING => true]
                ],
                false
            ],
            [
                false,
                [
                    [CreateProductAttributes::AVAILABLE_FOR_WRAPPING => false],
                    [CreateProductAttributes::AVAILABLE_FOR_WRAPPING => true]
                ],
                false
            ],
            [
                true,
                [
                    [CreateProductAttributes::AVAILABLE_FOR_WRAPPING => false],
                    [CreateProductAttributes::AVAILABLE_FOR_WRAPPING => false]
                ],
                false
            ],
            [
                true,
                [],
                false
            ]
        ];
    }
}
