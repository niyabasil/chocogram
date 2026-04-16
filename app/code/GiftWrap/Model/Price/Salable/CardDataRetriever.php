<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Price\Salable;

use Amasty\GiftWrap\Model\ConfigProvider;
use Amasty\GiftWrap\Model\Total\Quote\Tax\BeforeTax;

class CardDataRetriever implements RetrieverInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function execute(): array
    {
        return [
            'type' => BeforeTax::CARD_TYPE,
            'taxClassId' => $this->configProvider->getCardTaxClassId()
        ];
    }
}
