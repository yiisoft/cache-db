<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Throwable;
use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlFactory;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheTest extends AbstractDbCacheTest
{
    protected string $tableWithPrefix = 'pgsql_cache';

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotSupportedException
     * @throws Throwable
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new PgsqlFactory())->createConnection();

        // create migration
        DbHelper::ensureTable($this->db);

        parent::setUp();
    }
}
