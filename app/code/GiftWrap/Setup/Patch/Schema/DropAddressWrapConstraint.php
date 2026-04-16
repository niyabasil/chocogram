<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Setup\Patch\Schema;

use Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;

class DropAddressWrapConstraint implements SchemaPatchInterface
{
    public const CONSTRAINT_NAME = 'FK_E3D85D48851C0B007070EF44A4D6F3EA';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function apply(): void
    {
        $tableName = $this->resourceConnection->getTableName(SaleDataResourceInterface::QUOTE_ADDRESS_WRAP_TABLE);

        $this->resourceConnection->getConnection()->dropForeignKey(
            $tableName,
            self::CONSTRAINT_NAME
        );
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
