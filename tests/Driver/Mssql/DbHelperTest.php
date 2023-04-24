<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use PHPUnit\Framework\TestCase;
use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Support\MssqlHelper;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbHelperTest extends TestCase
{
    public function testEnsureTable(): void
    {
        $db = (new MssqlHelper())->createConnection();
        $table = '{{%cache}}';

        DbHelper::dropTable($db, '{{%cache}}');

        $this->assertNull($db->getTableSchema($table, true));
        $this->assertTrue(DbHelper::ensureTable($db, $table));
    }

    public function testDropTable(): void
    {
        $db = (new MssqlHelper())->createConnection();
        $table = '{{%cache}}';

        DbHelper::dropTable($db, $table);

        $this->assertNull($db->getTableSchema($table, true));
    }

    public function testEnsureTableWithoutDefaulTable(): void
    {
        $db = (new MssqlHelper())->createConnection();
        $table = '{{%cache}}';
        $table1 = '{{%test}}';

        DbHelper::dropTable($db, $table);

        $this->assertNull($db->getTableSchema($table, true));
        $this->assertTrue(DbHelper::ensureTable($db, $table1));

        $this->assertNotNull($db->getTableSchema($table1, true));

        DbHelper::dropTable($db, $table1);
    }
}
