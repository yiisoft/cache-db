<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Oracle;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\TestUtility\TestTrait;

/**
 * @group Oracle
 */
final class MigrationOracleTest extends MigrationTest
{
    use TestTrait;

    protected const DB_CONNECTION_CLASS = \Yiisoft\Db\Oracle\Connection::class;
    protected const DB_DRIVERNAME = 'oci';
    protected const DB_DSN = 'oci:dbname=localhost/XE;';
    protected const DB_FIXTURES_PATH = '';
    protected const DB_USERNAME = 'system';
    protected const DB_PASSWORD = 'oracle';
    protected const DB_CHARSET = 'AL32UTF8';

    public function setUp(): void
    {
        parent::setUp();

        /** @var ConnectionInterface */
        $this->db = $this->createConnection(self::DB_DSN);

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
