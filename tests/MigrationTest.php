<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

abstract class MigrationTest extends TestCase
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function testUpAndDown(): void
    {
        $migration = $this->createMigration();
        $migrationBuilder = $this->createMigrationBuilder();

        $migration->up($migrationBuilder);
        $this->assertTrue($this->tableExists('test-table'));
        $this->assertFalse($this->tableExists('table-not-exist'));

        $migration->down($migrationBuilder);
        $this->assertFalse($this->tableExists('test-table'));
        $this->assertFalse($this->tableExists('table-not-exist'));
    }

    private function tableExists(string $tableName): bool
    {
        return $this->db->getSchema()->getTableSchema($tableName) !== null;
    }
}
