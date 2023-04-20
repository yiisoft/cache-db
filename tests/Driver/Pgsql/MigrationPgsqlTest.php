<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Yiisoft\Cache\Db\Tests\Support\PgsqlHelper;
use Yiisoft\Cache\Db\Tests\TestCase;

/**
 * @group Pgsql
 */
final class MigrationPgsqlTest extends TestCase
{
    public function testCreateMigration(): void
    {
        $db = (new PgsqlHelper())->createConnection();
        $result = $this->createMigration($db);

        $this->assertSame(0, $result);
    }

    public function testCreateMigrationWithForce(): void
    {
        $db = (new PgsqlHelper())->createConnection();
        $result = $this->createMigration($db, true);

        $this->assertSame(0, $result);
    }
}
