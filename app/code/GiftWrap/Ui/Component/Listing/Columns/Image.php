<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Ui\Component\Listing\Columns;

use Amasty\GiftWrap\Api\Data\WrapInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Image extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Amasty\GiftWrap\Model\ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Amasty\GiftWrap\Model\ImageProcessor $imageProcessor,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName . '_src'] = $this->imageProcessor->getThumbnailUrl($item[WrapInterface::IMAGE]);
                $item[$fieldName . '_alt'] = $this->getAlt($item);
                $item[$fieldName . '_orig_src'] = $item[WrapInterface::IMAGE];
            }
        }

        return $dataSource;
    }

    /**
     * Get Alt
     *
     * @param array $row
     *
     * @return null|string
     */
    private function getAlt($row)
    {
        $altField = $this->getData('config/altField');
        return $row[$altField] ?? null;
    }
}
