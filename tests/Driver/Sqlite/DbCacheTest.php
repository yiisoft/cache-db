<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Throwable;
use Yiisoft\Cache\Db\Migration;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteFactory;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Sqlite
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheTest extends AbstractDbCacheTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new SqliteFactory())->createConnection();

        // create migration
        Migration::ensureTable($this->db);

        parent::setUp();
    }
}
