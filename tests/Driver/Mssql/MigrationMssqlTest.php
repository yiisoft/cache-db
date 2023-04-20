<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Yiisoft\Cache\Db\Tests\Support\MssqlHelper;
use Yiisoft\Cache\Db\Tests\TestCase;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationMssqlTest extends TestCase
{
    public function testCreateMigration(): void
    {
        $db = (new MssqlHelper())->createConnection();
        $result = $this->createMigration($db);

        $this->assertSame(0, $result);
    }

    public function testCreateMigrationWithForce(): void
    {
        $db = (new MssqlHelper())->createConnection();
        $result = $this->createMigration($db, true);

        $this->assertSame(0, $result);
    }
}
