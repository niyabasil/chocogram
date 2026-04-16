<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\GiftWrap\Test\Unit\Block\Product;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\GiftWrap\Block\Product\View;
use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\OptionSource\Allow;
use Amasty\GiftWrap\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\GiftWrap\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Product as Product;
use Magento\Framework\Registry;

/**
 * Class View
 *
 * @see View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    use ReflectionTrait;
    use ObjectManagerTrait;

    /**
     * @covers View::validate
     * @dataProvider validateDataProvider
     */
    public function testValidate($currentPage, $availablePages, $skipValidation, $availableForWrap, $isSaleable, $expectedResult)
    {
        if (!class_exists(ConfigProviderAbstract::class)) {
            $this->getMockBuilder(ConfigProviderAbstract::class)->getMock();
        }

        /** @var View $block */
        $block = $this->getObjectManager()->getObject(View::class);
        $configProvider = $this->createMock(ConfigProvider::class);
        $registry = $this->createMock(Registry::class);
        $product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['isSaleable'])
            ->addMethods(['getAmAvailableForWrapping'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configProvider->expects($this->any())->method('isEnabledOnPage')
            ->willReturnCallback(function ($currentPage) use ($availablePages) {
                return in_array($currentPage, $availablePages);
            });
        $product->expects($this->any())->method('getAmAvailableForWrapping')->willReturn($availableForWrap);
        $product->expects($this->any())->method('isSaleable')->willReturn($isSaleable);
        $registry->expects($this->any())->method('registry')->willReturn($product);
        $this->setProperty($block, 'visibility', $currentPage, View::class);
        $this->setProperty($block, 'skipValidation', $skipValidation, View::class);
        $this->setProperty($block, 'configProvider', $configProvider, View::class);
        $this->setProperty($block, 'registry', $registry, View::class);
        $this->assertEquals($expectedResult, $block->validate());
    }

    /**
     * Data provider for validate test
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [
                Allow::CART_PAGE,
                [Allow::CART_PAGE, Allow::CHECKOUT_PAGE],
                false,
                false,
                false,
                false
            ],
            [
                Allow::CART_PAGE,
                [Allow::CART_PAGE, Allow::CHECKOUT_PAGE],
                true,
                false,
                false,
                true
            ],
            [
                Allow::PRODUCT_PAGE,
                [Allow::PRODUCT_PAGE, Allow::CHECKOUT_PAGE],
                false,
                true,
                true,
                true
            ],
            [
                Allow::PRODUCT_PAGE,
                [Allow::CHECKOUT_PAGE],
                false,
                true,
                true,
                false
            ]
        ];
    }
}
