<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;

use function explode;
use function file_get_contents;
use function trim;

final class DbHelper
{
    public static function createMigration(DbCache $dbCache, bool $force = false): bool
    {
        $db = $dbCache->getDb();
        $command = $db->createCommand();
        $schema = $db->getSchema();
        $table = $dbCache->getTable();

        $existTable = $schema->getTableSchema($table, true);

        if ($existTable !== null && $force === false) {
            return false;
        }

        if ($force && $existTable !== null) {
            $command->dropTable($table)->execute();
        }

        $command->createTable(
            $table,
            [
                'id' => $schema->createColumn('string', 128)->notNull(),
                'data' => $schema->createColumn('binary'),
                'expire' => $schema->createColumn('integer'),
                'CONSTRAINT [PK_cache] PRIMARY KEY ([id])',
            ],
        )->execute();

        return true;
    }

    /**
     * Loads the fixture into the database.
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function createMigrationFromSqlDump(ConnectionInterface $db, string $fixture): void
    {
        $db->open();

        if (
            $db->getDriverName() === 'oci' &&
            ($statments = explode('/* STATEMENTS */', file_get_contents($fixture), 2)) &&
            count($statments) === 2
        ) {
            [$drops, $creates] = $statments;
            $lines = array_merge(explode('--', $drops), explode(';', $creates));
        } else {
            $lines = explode(';', file_get_contents($fixture));
        }

        foreach ($lines as $line) {
            $db->createCommand(trim($line))->execute();
        }
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
