<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\GiftWrap\Test\Unit\Block\Checkout\Item\Wrap;

use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\GiftWrap\Block\Checkout\Item\Wrap\Renderer;
use Amasty\GiftWrap\Setup\Patch\Data\CreateProductAttributes;
use Amasty\GiftWrap\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\GiftWrap\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Product;

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
     * @covers Renderer::enabledForProduct
     * @dataProvider enabledForProductDataProvider
     */
    public function testEnabledForProduct($data, $expected)
    {
        if (!class_exists(ConfigProviderAbstract::class)) {
            $this->getMockBuilder(ConfigProviderAbstract::class)->getMock();
        }

        $block = $this->getObjectManager()->getObject(Renderer::class);
        $product = $this->getObjectManager()->getObject(Product::class, ['data' => $data]);
        $this->assertEquals($expected, $this->invokeMethod($block, 'enabledForProduct', [$product]));
    }

    /**
     * Data provider for enabledForProduct test
     * @return array
     */
    public function enabledForProductDataProvider()
    {
        return [
            [
                [
                    'type_id' => 'simple',
                    CreateProductAttributes::AVAILABLE_FOR_WRAPPING => true
                ],
                true
            ],
            [
                [
                    'type_id' => 'giftcard',
                    'giftcard_type' => '1',
                    CreateProductAttributes::AVAILABLE_FOR_WRAPPING => true
                ],
                true
            ],
            [
                [
                    'type_id' => 'giftcard',
                    'giftcard_type' => '2',
                    CreateProductAttributes::AVAILABLE_FOR_WRAPPING => true
                ],
                false
            ],
            [
                [
                    'type_id' => 'simple',
                    CreateProductAttributes::AVAILABLE_FOR_WRAPPING => false
                ],
                false
            ]
        ];
    }
}
