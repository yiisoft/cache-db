<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use Yiisoft\Cache\Db\Migration;
use Yiisoft\Db\Constraint\IndexConstraint;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractMigrationTest extends TestCase
{
    public function testDropTable(): void
    {
        $table = '{{%cache}}';

        Migration::dropTable($this->db, $table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTable(): void
    {
        $table = '{{%cache}}';

        Migration::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema($table, true));

        Migration::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTableExist(): void
    {
        $table = '{{%cache}}';

        Migration::dropTable($this->db);

        $this->assertNull($this->db->getTableSchema($table, true));

        Migration::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table));

        Migration::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table));
    }

    public function testVerifyTableIndexes(): void
    {
        Migration::ensureTable($this->db);

        $schema = $this->db->getSchema();

        /** @psalm-var IndexConstraint[] $indexes */
        $indexes = $schema->getTableIndexes($this->dbCache->getTable(), true);

        $this->assertSame(['id'], $indexes[0]->getColumnNames());
        $this->assertTrue($indexes[0]->isUnique());
        $this->assertTrue($indexes[0]->isPrimary());
    }

    public function testVerifyTableStructure(): void
    {
        Migration::ensureTable($this->db);

        $prefix = $this->db->getTablePrefix();
        $tableSchema = $this->db->getTableSchema($this->dbCache->getTable());

        $this->assertNotNull($tableSchema);

        $this->assertSame($prefix . 'cache', $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'data', 'expire'], $tableSchema->getColumnNames());
        $this->assertSame('string', $tableSchema->getColumn('id')?->getType());
        $this->assertSame(128, $tableSchema->getColumn('id')?->getSize());
        $this->assertSame('binary', $tableSchema->getColumn('data')?->getType());
        $this->assertSame('integer', $tableSchema->getColumn('expire')?->getType());
    }
}
