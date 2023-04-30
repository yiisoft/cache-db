<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Throwable;
use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MssqlFactory;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Mssql
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
        $this->db = (new MssqlFactory())->createConnection();

        // create migration
        DbHelper::ensureTable($this->db);

        parent::setUp();
    }
}
