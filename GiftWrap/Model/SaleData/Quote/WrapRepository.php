<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\SaleData\Quote;

use Amasty\GiftWrap\Api\SaleData\WrapRepositoryInterface;
use Amasty\GiftWrap\Model\SaleData\AbstractWrapRepository;
use Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap as WrapResource;
use Amasty\GiftWrap\Model\SaleData\Quote\ResourceModel\Wrap\CollectionFactory;
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
