<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Throwable;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\DbSchemaManager;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Constant\ColumnType;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractDbSchemaManagerTest extends TestCase
{
    protected ConnectionInterface $db;
    private DbSchemaManager $dbSchemaManager;

    protected function setup(): void
    {
        parent::setUp();

        $this->dbSchemaManager = new DbSchemaManager($this->db);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();

        unset($this->db, $this->dbSchemaManager);
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableAndDropTable(string $table): void
    {
        $this->dbSchemaManager->ensureTable($table);

        $this->assertNotNull($this->db->getTableSchema($table, true));

        $this->dbSchemaManager->ensureNoTable($table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableExist(string $table): void
    {
        $this->dbSchemaManager->ensureTable($table);

        $this->assertNotNull($this->db->getTableSchema($table, true));

        $this->dbSchemaManager->ensureTable($table);

        $this->assertNotNull($this->db->getTableSchema($table, true));

        $this->dbSchemaManager->ensureNoTable($table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableIndexes(string $table): void
    {
        $dbCache = new DbCache($this->db, $table, 1_000_000);

        $this->dbSchemaManager->ensureTable($dbCache->getTable());

        $schema = $this->db->getSchema();

        $index = $schema->getTablePrimaryKey($dbCache->getTable(), true);

        $this->assertNotNull($index);
        $this->assertSame(['id'], $index->columnNames);
        $this->assertTrue($index->isUnique);
        $this->assertTrue($index->isPrimaryKey);

        $this->dbSchemaManager->ensureNoTable($dbCache->getTable());

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    /**
     * @dataProvider tableNameProvider
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableStructure(string $table): void
    {
        $dbCache = new DbCache($this->db, $table, 1_000_000);

        $this->dbSchemaManager->ensureTable($dbCache->getTable());

        $tableSchema = $this->db->getTableSchema($dbCache->getTable());
        $tableRawName = $this->db->getQuoter()->getRawTableName($dbCache->getTable());

        $this->assertNotNull($tableSchema);

        $this->assertSame($tableRawName, $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'data', 'expire'], $tableSchema->getColumnNames());
        $this->assertSame(ColumnType::STRING, $tableSchema->getColumn('id')?->getType());
        $this->assertSame(128, $tableSchema->getColumn('id')?->getSize());
        $this->assertSame(ColumnType::BINARY, $tableSchema->getColumn('data')?->getType());
        $this->assertSame(ColumnType::INTEGER, $tableSchema->getColumn('expire')?->getType());

        $this->dbSchemaManager->ensureNoTable($dbCache->getTable());

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    public static function tableNameProvider(): array
    {
        return [
            ['{{%yii_cache}}'],
            ['{{%custom_cache}}'],
        ];
    }
}
