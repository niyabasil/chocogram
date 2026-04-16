<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Setup\Declaration\Schema\Db\MySQL\DDL\Triggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DDLTriggerInterface;
use Magento\Framework\Setup\Declaration\Schema\ElementHistory;

class MigrateDataFromAnotherTableIfExists implements DDLTriggerInterface
{
    public const MATCH_PATTERN = '/amMigrateDataFromAnotherTableIfExists\(([^\)]+)\)/';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function isApplicable(string $statement): bool
    {
        return (bool) preg_match(self::MATCH_PATTERN, $statement);
    }

    public function getCallback(ElementHistory $elementHistory): callable
    {
        $table = $elementHistory->getNew();
        preg_match(self::MATCH_PATTERN, $table->getOnCreate(), $matches);

        return function () use ($table, $matches) {
            $tableName = $table->getName();
            $oldTableName = $this->resourceConnection->getTableName($matches[1]);
            $adapter = $this->resourceConnection->getConnection($table->getResource());

            if ($adapter->isTableExists($oldTableName)) {
                $select = $adapter->select()->from($oldTableName);
                $adapter->query($adapter->insertFromSelect($select, $tableName));
            }
        };
    }
}
