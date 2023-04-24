<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Sqlite;

use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\SqliteHelper;

/**
 * @group Sqlite
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheSqliteTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        $this->db = (new SqliteHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('sqlite3_');

        // create migration
        DbHelper::ensureTable($this->db, $this->table);

        parent::setUp();
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('sqlite3_cache', $this->db->getSchema()->getRawTableName('{{%cache}}'));
    }
}
