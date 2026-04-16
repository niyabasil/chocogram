<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface MessageCardSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\GiftWrap\Api\Data\MessageCardInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\GiftWrap\Api\Data\MessageCardInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
