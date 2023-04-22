<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlHelper;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Exception\NotSupportedException;

/**
 * @group Pgsql
 */
final class DbCachePgsqlTest extends AbstractDbCacheTest
{
    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new PgsqlHelper())->createConnection();

        // create db cache
        $this->dbCache = new DbCache($this->db, gcProbability: 1_000_000);
    }

    /**
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();

        unset($this->dbCache, $this->db);
    }
}
