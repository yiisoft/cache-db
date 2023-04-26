<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationTest extends AbstractMigrationTest
{
    protected function setup(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('mysql_');

        parent::setUp();
    }
}
