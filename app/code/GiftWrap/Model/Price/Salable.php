<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Price;

class Salable implements SalableInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var float
     */
    private $price;

    /**
     * @var int
     */
    private $qty;

    /**
     * @var int
     */
    private $taxClassId;

    public function __construct(int $id, string $type, float $price, int $qty, int $taxClassId)
    {
        $this->id = $id;
        $this->type = $type;
        $this->price = $price;
        $this->qty = $qty;
        $this->taxClassId = $taxClassId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSalableType(): string
    {
        return $this->type;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function getTaxClassId(): int
    {
        return $this->taxClassId;
    }
}
