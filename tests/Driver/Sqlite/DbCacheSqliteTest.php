<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Sqlite
 */
final class DbCacheSqliteTest extends DbCacheTest
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = (new SqliteHelper())->createConnection();

        // create connection dbms-specific
        $this->createMigration($this->db);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();
    }
}
