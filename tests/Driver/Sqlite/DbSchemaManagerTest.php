<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Throwable;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbSchemaManagerTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteFactory;

/**
 * @group Sqlite
 */
final class DbSchemaManagerTest extends AbstractDbSchemaManagerTest
{
    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new SqliteFactory())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('sqlite3_');

        parent::setUp();
    }
}
