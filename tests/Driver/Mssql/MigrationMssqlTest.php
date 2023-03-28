<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Cache\Db\Tests\Support\MssqlHelper;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationMssqlTest extends MigrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = (new MssqlHelper())->createConnection();

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
