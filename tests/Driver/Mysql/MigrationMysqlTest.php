<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationMysqlTest extends MigrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = (new MysqlHelper())->createConnection();

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
