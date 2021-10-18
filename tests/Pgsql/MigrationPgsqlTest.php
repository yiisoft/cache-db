<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Pgsql;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\TestUtility\TestTrait;

/**
 * @group Pgsql
 */
final class MigrationPgsqlTest extends MigrationTest
{
    use TestTrait;

    protected const DB_CONNECTION_CLASS = \Yiisoft\Db\Pgsql\Connection::class;
    protected const DB_DRIVERNAME = 'pgsql';
    protected const DB_DSN = 'pgsql:host=127.0.0.1;dbname=yiitest;port=5432';
    protected const DB_FIXTURES_PATH = __DIR__ . '/Fixture/postgres.sql';
    protected const DB_USERNAME = 'root';
    protected const DB_PASSWORD = 'root';
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
