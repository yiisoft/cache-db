<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;
use Yiisoft\Cache\Db\Tests\TestCase;
use Yiisoft\Db\Connection\ConnectionInterface;

/**
 * @group Sqlite
 */
final class MigrationSqliteTest extends TestCase
{
    public function testCreateMigration(): void
    {
        $db = (new SqliteHelper())->createConnection();
        $result = $this->createMigration($db);

        $this->assertSame(0, $result);
    }

    public function testCreateMigrationWithForce(): void
    {
        $db = (new SqliteHelper())->createConnection();
        $result = $this->createMigration($db, true);

        $this->assertSame(0, $result);
    }
}
