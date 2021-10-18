<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Oracle;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Db\TestUtility\TestTrait;

/**
 * @group Oracle
 */
final class DbCacheOracleTest extends DbCacheTest
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

        // create connection dbms specific
        $this->db = $this->createConnection(self::DB_DSN);
        $this->db->setAttributes([\PDO::ATTR_STRINGIFY_FETCHES => true]);

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
