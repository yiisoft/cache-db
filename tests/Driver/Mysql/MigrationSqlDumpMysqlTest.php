<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationSqlDumpMysqlTest extends AbstractMigrationTest
{
    protected function setup(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();

        // create migration
        $this->createMigrationFromSqlDump($this->db, dirname(__DIR__, 3) . '/src/Migration/schema-mysql.sql');

        parent::setUp();
    }
}
