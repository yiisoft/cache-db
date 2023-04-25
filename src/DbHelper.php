<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Schema\SchemaInterface;

final class DbHelper
{
    public static function ensureTable(ConnectionInterface $db, string $table = '{{%cache}}'): void
    {
        $command = $db->createCommand();
        $schema = $db->getSchema();

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

    public static function dropTable(ConnectionInterface $db, string $table = '{{%cache}}'): void
    {
        $command = $db->createCommand();

        if ($db->getTableSchema($table, true) !== null) {
            $command->dropTable($table)->execute();
        }
    }
}
