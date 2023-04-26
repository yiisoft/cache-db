<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;

/**
 * @group Sqlite
 */
final class MigrationTest extends AbstractMigrationTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new SqliteHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('sqlite3_');

        parent::setUp();
    }
}
