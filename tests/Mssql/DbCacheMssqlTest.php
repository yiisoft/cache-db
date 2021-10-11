<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Mssql;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\TestUtility\TestTrait;

/**
 * @group mssql
 */
final class DbCacheMssqlTest extends DbCacheTest
{
    use TestTrait;

    protected const DB_CONNECTION_CLASS = \Yiisoft\Db\Mssql\Connection::class;
    protected const DB_DRIVERNAME = 'mssql';
    protected const DB_DSN = 'sqlsrv:Server=127.0.0.1,1433;Database=yiitest';
    protected const DB_FIXTURES_PATH = '';
    protected const DB_USERNAME = 'SA';
    protected const DB_PASSWORD = 'YourStrong!Passw0rd';
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
