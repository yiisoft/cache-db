<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Yiisoft\Cache\Db\Tests\DbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MssqlHelper;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheMssqlTest extends DbCacheTest
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new MssqlHelper())->createConnection();

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
