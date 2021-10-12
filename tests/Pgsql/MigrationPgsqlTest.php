<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Pgsql;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\TestUtility\TestTrait;

final class MigrationPgsqlTest extends MigrationTest
{
    use TestTrait;

    protected const DB_CONNECTION_CLASS = \Yiisoft\Db\Pgsql\Connection::class;
    protected const DB_DRIVERNAME = 'pgsql';
    protected const DB_DSN = 'pgsql:host=127.0.0.1;dbname=scrutinizer;port=5432';
    protected const DB_FIXTURES_PATH = __DIR__ . '/Fixture/postgres.sql';
    protected const DB_USERNAME = 'scrutinizer';
    protected const DB_PASSWORD = 'scrutinizer';
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
