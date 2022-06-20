<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Sqlite;

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

        /** @var ConnectionInterface */
        $this->db = (new SqliteHelper())->createConnection();

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
