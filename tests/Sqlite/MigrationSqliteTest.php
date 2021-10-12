<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Sqlite;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\TestUtility\TestTrait;

final class MigrationSqliteTest extends MigrationTest
{
    use TestTrait;

    protected const DB_CONNECTION_CLASS = \Yiisoft\Db\Sqlite\Connection::class;
    protected const DB_DRIVERNAME = 'sqlite';
    protected const DB_DSN = 'sqlite:' . __DIR__ . '/../runtime/test.sq3';
    protected const DB_FIXTURES_PATH = '';
    protected const DB_USERNAME = '';
    protected const DB_PASSWORD = '';
    protected const DB_CHARSET = 'UTF8';

    public function setUp(): void
    {
        parent::setUp();

        /** @var ConnectionInterface */
        $this->db = $this->createConnection(self::DB_DSN);

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
