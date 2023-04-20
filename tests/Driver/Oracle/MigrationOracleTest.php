<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\Tests\Support\OracleHelper;
use Yiisoft\Cache\Db\Tests\MigrationTest;

/**
 * @group Oracle
 */
final class MigrationOracleTest extends MigrationTest
{
    protected function setup(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new OracleHelper())->createConnection();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();
    }
}
