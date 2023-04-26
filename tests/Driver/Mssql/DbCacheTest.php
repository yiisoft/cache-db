<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Yiisoft\Cache\Db\Migration;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\MssqlHelper;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;

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
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MssqlHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('mssql_');

        // create migration
        Migration::ensureTable($this->db);

        parent::setUp();
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('mssql_cache', $this->db->getSchema()->getRawTableName('{{%cache}}'));
    }
}
