<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Ui\Component\Listing\Columns\MessageCard;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->escaper = $escaper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $item[$name]['edit'] = [
                    'href'  => $this->urlBuilder->getUrl(
                        'amgiftwrap/message_card/edit',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('Edit')
                ];

                $item[$name]['duplicate'] = [
                    'href'  => $this->urlBuilder->getUrl(
                        'amgiftwrap/message_card/duplicate',
                        ['id' => $item['entity_id']]
                    ),
                    'label' => __('Duplicate')
                ];

                $title = $this->escaper->escapeHtml($item['name'] ?? '');
                $item[$name]['delete'] = [
                    'href'    => $this->urlBuilder->getUrl(
                        'amgiftwrap/message_card/delete',
                        ['id' => $item['entity_id']]
                    ),
                    'label'   => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete %1', $title),
                        'message' => __('Are you sure you wan\'t to delete "%1" gift message card?', $title)
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
