<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

final class DbHelper
{
    public static function ensureTable(DbCache $dbCache): bool
    {
        $db = $dbCache->getDb();
        $command = $db->createCommand();
        $schema = $db->getSchema();
        $table = $dbCache->getTable();

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

    public static function dropTable(DbCache $dbCache): void
    {
        $db = $dbCache->getDb();
        $command = $db->createCommand();
        $table = $dbCache->getTable();

        if ($db->getTableSchema($table, true) !== null) {
            $command->dropTable($table)->execute();
        }
    }
}
