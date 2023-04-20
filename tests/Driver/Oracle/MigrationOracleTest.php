<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\Tests\Support\OracleHelper;
use Yiisoft\Cache\Db\Tests\TestCase;

/**
 * @group Oracle
 */
final class MigrationOracleTest extends TestCase
{
    public function testCreateMigration(): void
    {
        $db = (new OracleHelper())->createConnection();
        $result = $this->createMigration($db);

        $this->assertSame(0, $result);
    }

    public function testCreateMigrationWithForce(): void
    {
        $db = (new OracleHelper())->createConnection();
        $result = $this->createMigration($db, true);

        $this->assertSame(0, $result);
    }
}
