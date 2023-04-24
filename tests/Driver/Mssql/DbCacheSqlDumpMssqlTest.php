<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MssqlHelper;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheSqlDumpMssqlTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MssqlHelper())->createConnection();

        // create migration
        $this->createMigrationFromSqlDump($this->db, dirname(__DIR__, 3) . '/src/Migration/schema-mssql.sql');

        parent::setUp();
    }
}
