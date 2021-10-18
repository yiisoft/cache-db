<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Mssql;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\TestUtility\TestTrait;

/**
 * @group Mssql
 */
final class MigrationMssqlTest extends MigrationTest
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

        /** @var ConnectionInterface */
        $this->db = $this->createConnection(self::DB_DSN);

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
