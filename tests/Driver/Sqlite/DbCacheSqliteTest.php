<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;

/**
 * @group Sqlite
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheSqliteTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = (new SqliteHelper())->createConnection();

        // create db cache
        $this->dbCache = new DbCache($this->db, gcProbability: 1_000_000);
    }
}
