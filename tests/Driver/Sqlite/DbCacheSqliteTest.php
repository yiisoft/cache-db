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

        // create connection dbms-specific
        $this->db = (new SqliteHelper())->createConnection();

        // create cache instance
        $this->dbCache = $this->createDbCache();

        // create migration table
        $migration = $this->createMigration();
        $migration->up($this->createMigrationBuilder());
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function tearDown(): void
    {
        // remove migration table
        $migration = $this->createMigration();
        $migration->down($this->createMigrationBuilder());

        $this->db->close();
    }
}
