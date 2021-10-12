<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Mysql;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Db\TestUtility\TestTrait;

final class DbCacheMysqlTest extends DbCacheTest
{
    use TestTrait;

    protected const DB_CONNECTION_CLASS = \Yiisoft\Db\Mysql\Connection::class;
    protected const DB_DRIVERNAME = 'mysql';
    protected const DB_DSN = 'mysql:host=127.0.0.1;dbname=yiitest;port=3306';
    protected const DB_FIXTURES_PATH = '';
    protected const DB_USERNAME = 'root';
    protected const DB_PASSWORD = '';
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
