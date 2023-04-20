<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;

/**
 * @group Sqlite
 */
final class MigrationSqliteTest extends MigrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new SqliteHelper())->createConnection();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();
    }
}
