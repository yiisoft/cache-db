<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\MssqlHelper;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationMssqlTest extends AbstractMigrationTest
{
    protected function setup(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new MssqlHelper())->createConnection();

        // create db cache
        $this->dbCache = new DbCache($this->db);
    }
}
