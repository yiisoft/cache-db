<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;

/**
 * @group Sqlite
 */
final class MigrationSqliteTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new SqliteHelper())->createConnection();

        // create db cache
        $this->dbCache = new DbCache($this->db, gcProbability: 1_000_000);
    }
}
