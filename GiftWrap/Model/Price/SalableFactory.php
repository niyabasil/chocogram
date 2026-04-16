<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Price;

use Amasty\GiftWrap\Model\MessageCard\MessageCard;
use Amasty\GiftWrap\Model\Price\Salable\RetrieverInterface;
use Amasty\GiftWrap\Model\Wrapper\Wrap;
use InvalidArgumentException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManagerInterface;

class SalableFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var RetrieverInterface[]
     */
    private $retrievers;

    public function __construct(
        ObjectManagerInterface $objectManager,
        array $retrievers = []
    ) {
        $this->objectManager = $objectManager;
        $this->retrievers = $retrievers;
    }

    /**
     * @param Wrap|MessageCard|AbstractModel $model
     * @param int $qty
     * @return SalableInterface
     */
    public function create(AbstractModel $model, int $qty = 1): SalableInterface
    {
        $retriever = $this->retrievers[get_class($model)] ?? null;
        if ($retriever === null) {
            throw new InvalidArgumentException(__('Can\'t create salable item for class: %1', get_class($model)));
        }

        $data = $retriever->execute();
        $data['qty'] = $qty;
        $data['price'] = $model->getPrice();
        $data['id'] = (int) $model->getId();

        return $this->objectManager->create(Salable::class, $data);
    }
}
