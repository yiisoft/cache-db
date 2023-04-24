<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlHelper;

/**
 * @group Pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheSqlDumpPgsqlTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new PgsqlHelper())->createConnection();

        // create migration
        $this->createMigrationFromSqlDump($this->db, dirname(__DIR__, 3) . '/src/Migration/schema-pgsql.sql');

        parent::setUp();
    }
}
