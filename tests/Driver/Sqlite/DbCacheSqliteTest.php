<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Sqlite
 */
final class DbCacheSqliteTest extends AbstractDbCacheTest
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = (new SqliteHelper())->createConnection();

        // create db cache
        $this->dbCache = new DbCache($this->db, gcProbability: 1_000_000);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();

        unset($this->dbCache, $this->db);
    }
}
