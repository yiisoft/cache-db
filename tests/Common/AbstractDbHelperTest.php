<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Constraint\IndexConstraint;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;
use Yiisoft\Db\Schema\SchemaInterface;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractDbHelperTest extends TestCase
{
    protected ConnectionInterface $db;

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testDropTable(): void
    {
        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema('{{%cache}}', true));

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%cache}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testDropTableWithCustomTableName(): void
    {
        DbHelper::ensureTable($this->db, '{{%custom_cache}}');

        $this->assertNotNull($this->db->getTableSchema('{{%custom_cache}}', true));

        DbHelper::dropTable($this->db, '{{%custom_cache}}');

        $this->assertNull($this->db->getTableSchema('{{%custom_cache}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTable(): void
    {
        DbHelper::ensureTable($this->db);

        $this->assertNotNull($this->db->getTableSchema('{{%cache}}', true));

        DbHelper::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema('{{%cache}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableWithCustomTableName(): void
    {
        DbHelper::ensureTable($this->db, '{{%custom_cache}}');

        $this->assertNotNull($this->db->getTableSchema('{{%custom_cache}}', true));

        DbHelper::dropTable($this->db, '{{%custom_cache}}');

        $this->assertNull($this->db->getTableSchema('{{%custom_cache}}', true));
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Exception
     * @throws Throwable
     */
    public function testEnsureTableExist(): void
    {
        $prefix = $this->db->getTablePrefix();

        try {
            DbHelper::ensureTable($this->db);
            DbHelper::ensureTable($this->db);
        } catch (RuntimeException $e) {
            $this->assertSame("Table \"{$prefix}cache\" already exists.", $e->getMessage());

            DbHelper::dropTable($this->db);

            $this->assertNull($this->db->getTableSchema('{{%cache}}', true));
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Exception
     * @throws Throwable
     */
    public function testEnsureTableExistWithCustomTableName(): void
    {
        $prefix = $this->db->getTablePrefix();

        try {
            DbHelper::ensureTable($this->db, '{{%custom_cache}}');
            DbHelper::ensureTable($this->db, '{{%custom_cache}}');
        } catch (RuntimeException $e) {
            $this->assertSame("Table \"{$prefix}custom_cache\" already exists.", $e->getMessage());

            DbHelper::dropTable($this->db, '{{%custom_cache}}');

            $this->assertNull($this->db->getTableSchema('{{%custom_cache}}', true));
        }
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableIndexes(): void
    {
        $dbCache = new DbCache($this->db, '{{%cache}}', 1_000_000);

        DbHelper::ensureTable($this->db, $dbCache->getTable());

        $schema = $this->db->getSchema();

        /** @psalm-var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($dbCache->getTable(), true);

        $this->assertSame(['id'], $indexes[0]->getColumnNames());
        $this->assertTrue($indexes[0]->isUnique());
        $this->assertTrue($indexes[0]->isPrimary());

        DbHelper::dropTable($this->db, $dbCache->getTable());

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableIndexesWithCustomTableName(): void
    {
        $dbCache = new DbCache($this->db, '{{%custom_cache}}', 1_000_000);

        DbHelper::ensureTable($this->db, $dbCache->getTable());

        $schema = $this->db->getSchema();

        /** @psalm-var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($dbCache->getTable(), true);

        $this->assertSame(['id'], $indexes[0]->getColumnNames());
        $this->assertTrue($indexes[0]->isUnique());
        $this->assertTrue($indexes[0]->isPrimary());

        DbHelper::dropTable($this->db, $dbCache->getTable());

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableStructure(): void
    {
        $dbCache = new DbCache($this->db, '{{%cache}}', 1_000_000);

        DbHelper::ensureTable($this->db, $dbCache->getTable());

        $prefix = $this->db->getTablePrefix();
        $tableSchema = $this->db->getTableSchema($dbCache->getTable());

        $this->assertNotNull($tableSchema);

        $this->assertSame($prefix . 'cache', $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'data', 'expire'], $tableSchema->getColumnNames());
        $this->assertSame(SchemaInterface::TYPE_STRING, $tableSchema->getColumn('id')?->getType());
        $this->assertSame(128, $tableSchema->getColumn('id')?->getSize());
        $this->assertSame(SchemaInterface::TYPE_BINARY, $tableSchema->getColumn('data')?->getType());
        $this->assertSame(SchemaInterface::TYPE_INTEGER, $tableSchema->getColumn('expire')?->getType());

        DbHelper::dropTable($this->db, $dbCache->getTable());

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableStructureWithCustomTableName(): void
    {
        $dbCache = new DbCache($this->db, '{{%custom_cache}}', 1_000_000);

        DbHelper::ensureTable($this->db, $dbCache->getTable());

        $prefix = $this->db->getTablePrefix();
        $tableSchema = $this->db->getTableSchema($dbCache->getTable());

        $this->assertNotNull($tableSchema);

        $this->assertSame($prefix . 'custom_cache', $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'data', 'expire'], $tableSchema->getColumnNames());
        $this->assertSame(SchemaInterface::TYPE_STRING, $tableSchema->getColumn('id')?->getType());
        $this->assertSame(128, $tableSchema->getColumn('id')?->getSize());
        $this->assertSame(SchemaInterface::TYPE_BINARY, $tableSchema->getColumn('data')?->getType());
        $this->assertSame(SchemaInterface::TYPE_INTEGER, $tableSchema->getColumn('expire')?->getType());

        DbHelper::dropTable($this->db, $dbCache->getTable());

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }
}
