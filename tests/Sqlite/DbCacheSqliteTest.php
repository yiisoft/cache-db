<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Sqlite;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Db\TestUtility\TestTrait;

final class DbCacheSqliteTest extends DbCacheTest
{
    use TestTrait;

    protected const DB_CONNECTION_CLASS = \Yiisoft\Db\Sqlite\Connection::class;
    protected const DB_DRIVERNAME = 'sqlite';
    protected const DB_DSN = 'sqlite:' . __DIR__ . '/../runtime/test.sq3';
    protected const DB_FIXTURES_PATH = '';
    protected const DB_USERNAME = 'scrutinizer';
    protected const DB_PASSWORD = 'scrutinizer';
    protected const DB_CHARSET = 'UTF8';

    public function setUp(): void
    {
        parent::setUp();

        // create connection dbms specific
        $this->db = $this->createConnection(self::DB_DSN);

        // create cache instance
        $this->dbCache = $this->createDbCache();

        // create migration table
        $migration = $this->createMigration();
        $migration->up($this->createMigrationBuilder());
    }

    protected function tearDown(): void
    {
        // remove migration table
        $migration = $this->createMigration();
        $migration->down($this->createMigrationBuilder());

        $this->db->close();
    }
}
