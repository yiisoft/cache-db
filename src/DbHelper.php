<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use Yiisoft\Db\Connection\ConnectionInterface;

final class DbHelper
{
    public static function ensureTable(ConnectionInterface $db, string $table): bool
    {
        $command = $db->createCommand();
        $schema = $db->getSchema();

        if ($schema->getTableSchema($table, true) !== null) {
            return false;
        }

        $command->createTable(
            $table,
            [
                'id' => $schema->createColumn('string', 128)->notNull(),
                'data' => $schema->createColumn('binary'),
                'expire' => $schema->createColumn('integer'),
                'CONSTRAINT [[PK_cache]] PRIMARY KEY ([[id]])',
            ],
        )->execute();

        return true;
    }

    public static function dropTable(ConnectionInterface $db, string $table): void
    {
        $command = $db->createCommand();

        if ($db->getTableSchema($table, true) !== null) {
            $command->dropTable($table)->execute();
        }
    }
}
