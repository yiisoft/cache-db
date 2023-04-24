<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlHelper;

/**
 * @group Pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCachePgsqlTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new PgsqlHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('pgsql_');

        // create migration
        DbHelper::ensureTable($this->db, $this->table);

        parent::setUp();
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('pgsql_cache', $this->db->getSchema()->getRawTableName('{{%cache}}'));
    }
}
