<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;
use Yiisoft\Cache\Db\Tests\TestCase;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationMysqlTest extends TestCase
{
    public function testCreateMigration(): void
    {
        $db = (new MysqlHelper())->createConnection();
        $result = $this->createMigration($db);

        $this->assertSame(0, $result);
    }

    public function testCreateMigrationWithForce(): void
    {
        $db = (new MysqlHelper())->createConnection();
        $result = $this->createMigration($db, true);

        $this->assertSame(0, $result);
    }
}
