<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Mysql
 */
final class DbCacheMysqlTest extends DbCacheTest
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();

        // create connection dbms-specific
        $this->createMigration($this->db);
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();
    }
}
