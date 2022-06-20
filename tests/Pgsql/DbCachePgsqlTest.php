<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Pgsql;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlHelper;

/**
 * @group Pgsql
 */
final class DbCachePgsqlTest extends DbCacheTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms specific
        $this->db = (new PgsqlHelper())->createConnection();

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
