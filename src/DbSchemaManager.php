<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db;

use Throwable;
use Yiisoft\Db\Command\CommandInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Schema\SchemaInterface;

/**
 * Manages the cache table schema in the database.
 */
final class DbSchemaManager
{
    private CommandInterface $command;
    private SchemaInterface $schema;

    public function __construct(private ConnectionInterface $db)
    {
        $this->command = $db->createCommand();
        $this->schema = $db->getSchema();
    }

    /**
     * Ensures that the cache table exists in the database.
     *
     * @param string $table The name of the cache table. Defaults to '{{%yii_cache}}'.
     *
     * @throws Exception
     * @throws NotSupportedException
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function ensureTable(string $table = '{{%yii_cache}}'): void
    {
        $tableRawName = $this->schema->getRawTableName($table);

        if ($this->hasTable($table)) {
            return;
        }

        $this->command->createTable(
            $table,
            [
                'id' => $this->schema->createColumn(SchemaInterface::TYPE_STRING, 128)->notNull(),
                'data' => $this->schema->createColumn(SchemaInterface::TYPE_BINARY),
                'expire' => $this->schema->createColumn(SchemaInterface::TYPE_INTEGER),
                "CONSTRAINT [[PK_$tableRawName]] PRIMARY KEY ([[id]])",
            ],
        )->execute();
    }

    /**
     * Ensures that the cache table does not exist in the database.
     *
     * @param string $table The name of the cache table. Defaults to '{{%yii_cache}}'.
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function ensureNoTable(string $table = '{{%yii_cache}}'): void
    {
        $tableRawName = $this->schema->getRawTableName($table);

        if ($this->hasTable($table)) {
            $this->command->dropTable($tableRawName)->execute();
        }
    }

    /**
     * Checks if the given table exists in the database.
     *
     * @param string $table The name of the table to check.
     *
     * @return bool Whether the table exists or not.
     */
    private function hasTable(string $table): bool
    {
        return $this->db->getTableSchema($table, true) !== null;
    }
}
