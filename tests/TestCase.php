<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use PHPUnit\Framework\TestCase as AbstractTestCase;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Migration\M202101140204CreateCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Yii\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Yii\Db\Migration\MigrationBuilder;

abstract class TestCase extends AbstractTestCase
{
    protected ConnectionInterface $db;
    protected DbCache $dbCache;

    protected function createDbCache(): DbCache
    {
        return new DbCache($this->db, 'test-table');
    }

    protected function createMigration(): M202101140204CreateCache
    {
        return new M202101140204CreateCache($this->dbCache, new NullMigrationInformer());
    }

    protected function createMigrationBuilder(): MigrationBuilder
    {
        return new MigrationBuilder($this->db, new NullMigrationInformer());
    }
}
