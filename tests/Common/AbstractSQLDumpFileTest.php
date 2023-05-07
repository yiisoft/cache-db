<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Throwable;
use Yiisoft\Cache\Db\DbCache;
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
abstract class AbstractSQLDumpFileTest extends TestCase
{
    protected ConnectionInterface $db;
    private string $driverName = '';

    protected function setup(): void
    {
        parent::setUp();

        $this->driverName = $this->db->getDriverName();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();

        unset($this->db, $this->driverName);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testEnsureTableAndDropTable(): void
    {
        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

        $this->assertNotNull($this->db->getTableSchema('{{%yii_cache}}', true));

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema('{{%yii_cache}}', true));
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    public function testVerifyTableIndexes(): void
    {
        $dbCache = new DbCache($this->db, gcProbability: 1_000_000);

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

        $schema = $this->db->getSchema();

        /** @psalm-var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($dbCache->getTable(), true);

        $this->assertSame(['id'], $indexes[0]->getColumnNames());
        $this->assertTrue($indexes[0]->isUnique());
        $this->assertTrue($indexes[0]->isPrimary());

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

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
        $dbCache = new DbCache($this->db, gcProbability: 1_000_000);

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

        $tableSchema = $this->db->getTableSchema($dbCache->getTable());
        $tableRawName = $this->db->getSchema()->getRawTableName($dbCache->getTable());

        $this->assertNotNull($tableSchema);

        $this->assertSame($tableRawName, $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'data', 'expire'], $tableSchema->getColumnNames());
        $this->assertSame(SchemaInterface::TYPE_STRING, $tableSchema->getColumn('id')?->getType());
        $this->assertSame(128, $tableSchema->getColumn('id')?->getSize());
        $this->assertSame(SchemaInterface::TYPE_BINARY, $tableSchema->getColumn('data')?->getType());
        $this->assertSame(SchemaInterface::TYPE_INTEGER, $tableSchema->getColumn('expire')?->getType());

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    /**
     * Loads the fixture into the database.
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    private function loadFromSQLDumpFile(string $fixture): void
    {
        $this->db->open();

        $lines = explode(';', file_get_contents($fixture));

        foreach ($lines as $line) {
            $this->db->createCommand(trim($line))->execute();
        }
    }
}
