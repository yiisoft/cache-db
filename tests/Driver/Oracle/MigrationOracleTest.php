<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationOracleTest extends AbstractMigrationTest
{
    protected function setup(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new OracleHelper())->createConnection();

        // create db cache
        $this->dbCache = new DbCache($this->db, gcProbability: 1_000_000);
    }
}
