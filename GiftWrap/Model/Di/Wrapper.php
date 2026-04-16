<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Di;

use Magento\Framework\ObjectManagerInterface;

class Wrapper
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManagerInterface;

    /**
     * @var string
     */
    private $name;

    public function __construct(
        ObjectManagerInterface $objectManagerInterface,
        string $name = ''
    ) {
        $this->objectManagerInterface = $objectManagerInterface;
        $this->name = $name;
    }

    /**
     * @param string $name
     * @param array|null $arguments
     *
     * @return bool|mixed
     */
    public function __call(string $name, ?array $arguments = [])
    {
        $result = false;
        if ($this->instanceExists()) {
            $object = $this->objectManagerInterface->create($this->name);

            // @codingStandardsIgnoreLine
            $result = call_user_func_array([$object, $name], $arguments);
        }

        return $result;
    }

    private function instanceExists(): bool
    {
        return $this->name && (class_exists($this->name) || interface_exists($this->name));
    }
}
