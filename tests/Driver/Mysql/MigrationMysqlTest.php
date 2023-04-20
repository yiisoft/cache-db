<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;
use Yiisoft\Cache\Db\Tests\MigrationTest;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationMysqlTest extends MigrationTest
{
    protected function setup(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();
    }
}
