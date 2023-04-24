<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheMysqlTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('mysql_');

        // create migration
        DbHelper::ensureTable($this->db, $this->table);

        parent::setup();
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('mysql_cache', $this->db->getSchema()->getRawTableName('{{%cache}}'));
    }
}
