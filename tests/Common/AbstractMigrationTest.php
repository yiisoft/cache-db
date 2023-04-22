<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use Yiisoft\Cache\Db\DbCache;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractMigrationTest extends TestCase
{
    protected DbCache $dbCache;

    public function testVerifyTableStructure(): void
    {
        $table = $this->db->getTableSchema($this->dbCache->getTable());

        $this->assertNotNull($table);

        $this->assertSame('cache', $table->getName());
        $this->assertSame(['id'], $table->getPrimaryKey());
        $this->assertSame('string', $table->getColumn('id')->getType());
        $this->assertSame(128, $table->getColumn('id')->getSize());
        $this->assertSame('binary', $table->getColumn('data')->getType());
        $this->assertSame('integer', $table->getColumn('expire')->getType());
    }
}
