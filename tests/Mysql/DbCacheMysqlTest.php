<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Mysql;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 */
final class DbCacheMysqlTest extends DbCacheTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms specific
        $this->db = (new MysqlHelper())->createConnection();

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
