<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractMigrationTest extends TestCase
{
    public function testVerifyTableStructure(): void
    {
        $tableSchema = $this->db->getTableSchema($this->dbCache->getTable());

        $this->assertNotNull($tableSchema);

        $this->assertSame('cache', $tableSchema->getName());
        $this->assertSame(['id'], $tableSchema->getPrimaryKey());
        $this->assertSame(['id', 'data', 'expire'], $tableSchema->getColumnNames());
        $this->assertSame('string', $tableSchema->getColumn('id')->getType());
        $this->assertSame(128, $tableSchema->getColumn('id')->getSize());
        $this->assertSame('binary', $tableSchema->getColumn('data')->getType());
        $this->assertSame('integer', $tableSchema->getColumn('expire')->getType());
    }
}
