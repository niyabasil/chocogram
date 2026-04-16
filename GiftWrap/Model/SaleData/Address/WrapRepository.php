<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Address;

use Amasty\GiftWrap\Api\SaleData\WrapRepositoryInterface;
use Amasty\GiftWrap\Model\SaleData\AbstractWrapRepository;
use Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap as WrapResource;
use Amasty\GiftWrap\Model\SaleData\Address\ResourceModel\Wrap\CollectionFactory;
use Amasty\GiftWrap\Model\SaleData\Address\WrapFactory;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class WrapRepository extends AbstractWrapRepository implements WrapRepositoryInterface
{
    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        WrapFactory $wrapFactory,
        WrapResource $wrapResource,
        CollectionFactory $wrapCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->wrapFactory = $wrapFactory;
        $this->wrapResource = $wrapResource;
        $this->wrapCollectionFactory = $wrapCollectionFactory;
    }
}
