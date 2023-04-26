<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\Migration;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;

/**
 * @group Sqlite
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new SqliteHelper())->createConnection();

        // create migration
        Migration::ensureTable($this->db);

        parent::setUp();
    }
}
