<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use Throwable;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Schema\SchemaInterface;

use function in_array;
use function sprintf;

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

        self::validateSupportedDatabase($db);

        if ($schema->getTableSchema($table, true) !== null) {
            return;
        }

        $command->createTable(
            $table,
            [
                'id' => $schema->createColumn(SchemaInterface::TYPE_STRING, 128)->notNull(),
                'data' => $schema->createColumn(SchemaInterface::TYPE_BINARY),
                'expire' => $schema->createColumn(SchemaInterface::TYPE_INTEGER),
                'CONSTRAINT [[PK_cache]] PRIMARY KEY ([[id]])',
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

        self::validateSupportedDatabase($db);

        if ($db->getTableSchema($table, true) !== null) {
            $command->dropTable($table)->execute();
        }
    }

    private static function validateSupportedDatabase(ConnectionInterface $db): void
    {
        $driverName = $db->getDriverName();

        if (!in_array($driverName, ['mysql', 'oci', 'pgsql', 'sqlite', 'sqlsrv'], true)) {
            throw new NotSupportedException(
                sprintf(
                    'Database driver `%s` is not supported.',
                    $driverName,
                ),
            );
        }
    }
}
