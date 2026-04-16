<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Price;

interface SalableInterface
{
    public function getPrice(): float;

    public function getSalableType(): string;

    public function getQty(): int;

    public function getTaxClassId(): int;
}
