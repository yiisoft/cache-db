<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use PHPUnit\Framework\TestCase;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Constant\ColumnType;

use function dirname;

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

    public function testEnsureTableAndDropTable(): void
    {
        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

        $this->assertNotNull($this->db->getTableSchema('{{%yii_cache}}', true));

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema('{{%yii_cache}}', true));
    }

    public function testVerifyTableIndexes(): void
    {
        $dbCache = new DbCache($this->db, gcProbability: 1_000_000);

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

        $schema = $this->db->getSchema();

        $index = $schema->getTablePrimaryKey($dbCache->getTable(), true);

        $this->assertNotNull($index);
        $this->assertSame(['id'], $index->columnNames);
        $this->assertTrue($index->isPrimaryKey);
        $this->assertTrue($index->isUnique);

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    public function testVerifyTableStructure(): void
    {
        $dbCache = new DbCache($this->db, gcProbability: 1_000_000);

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-up.sql");

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

        $this->loadFromSQLDumpFile(dirname(__DIR__, 2) . "/sql/$this->driverName-down.sql");

        $this->assertNull($this->db->getTableSchema($dbCache->getTable(), true));
    }

    /**
     * Loads the fixture into the database.
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
