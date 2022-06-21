<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Mysql;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 */
final class MigrationMysqlTest extends MigrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ConnectionInterface */
        $this->db = (new MysqlHelper())->createConnection();

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
