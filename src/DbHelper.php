<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use RuntimeException;
use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Schema\SchemaInterface;

final class DbHelper
{
    /**
     * @throws Exception
     * @throws NotSupportedException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public static function ensureTable(ConnectionInterface $db, string $table = '{{%cache}}'): void
    {
        $command = $db->createCommand();
        $schema = $db->getSchema();
        $tableRawName = $schema->getRawTableName($table);

        if (self::hasTable($db, $table)) {
            throw new RuntimeException("Table \"$tableRawName\" already exists.");
        }

        $command->createTable(
            $table,
            [
                'id' => $schema->createColumn(SchemaInterface::TYPE_STRING, 128)->notNull(),
                'data' => $schema->createColumn(SchemaInterface::TYPE_BINARY),
                'expire' => $schema->createColumn(SchemaInterface::TYPE_INTEGER),
                "CONSTRAINT [[PK_{$tableRawName}]] PRIMARY KEY ([[id]])",
            ],
        )->execute();
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public static function dropTable(ConnectionInterface $db, string $table = '{{%cache}}'): void
    {
        $command = $db->createCommand();
        $schema = $db->getSchema();
        $tableRawName = $schema->getRawTableName($table);

        if (self::hasTable($db, $table)) {
            $command->dropTable($tableRawName)->execute();
        }
    }

    private static function hasTable(ConnectionInterface $db, string $table): bool
    {
        return $db->getTableSchema($table, true) !== null;
    }
}
