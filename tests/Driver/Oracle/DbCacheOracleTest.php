<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheOracleTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('oci_');

        // create migration
        DbHelper::ensureTable($this->db, $this->table);

        parent::setUp();
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('oci_cache', $this->db->getSchema()->getRawTableName('{{%cache}}'));
    }
}
