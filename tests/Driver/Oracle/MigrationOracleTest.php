<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Cache\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 */
final class MigrationOracleTest extends MigrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = (new OracleHelper())->createConnection();

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
