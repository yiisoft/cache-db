<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Migration\M202101140204CreateCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\Db\Migration\Informer\MigrationInformerInterface;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;

final class MigrationTest extends TestCase
{
    public function testUpAndDown(): void
    {
        $migration = new M202101140204CreateCache(
            $this->getContainer()->get(DbCache::class),
            $this->getContainer()->get(MigrationInformerInterface::class),
        );

        $migration->up($this->getContainer()->get(MigrationBuilder::class));

        $this->assertTrue($this->tableExists('test-table'));
        $this->assertFalse($this->tableExists('table-not-exist'));

        $migration->down($this->getContainer()->get(MigrationBuilder::class));

        $this->assertFalse($this->tableExists('test-table'));
        $this->assertFalse($this->tableExists('table-not-exist'));
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->getContainer()->get(ConnectionInterface::class)
            ->createCommand("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='{$table}'")
            ->queryScalar()
        ;
    }
}
