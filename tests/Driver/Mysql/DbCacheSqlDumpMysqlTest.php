<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheSqlDumpMysqlTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();

        // create migration
        $this->createMigrationFromSqlDump($this->db, dirname(__DIR__, 3) . '/src/Migration/schema-mysql.sql');

        parent::setUp();
    }
}
