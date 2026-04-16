<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model;

use Amasty\GiftWrap\Api\Data\MessageCardSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class MessageCardSearchResults extends SearchResults implements MessageCardSearchResultsInterface
{

}
