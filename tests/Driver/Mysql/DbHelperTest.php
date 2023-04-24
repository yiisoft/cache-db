<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use PHPUnit\Framework\TestCase;
use Yiisoft\Cache\Db\DbHelper;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbHelperTest extends TestCase
{
    public function testEnsureTable(): void
    {
        $db = (new MysqlHelper())->createConnection();
        $table = '{{%cache}}';

        DbHelper::dropTable($db, '{{%cache}}');

        $this->assertNull($db->getTableSchema($table, true));
        $this->assertTrue(DbHelper::ensureTable($db, $table));

        $db->close();
    }

    public function testDropTable(): void
    {
        $db = (new MysqlHelper())->createConnection();
        $table = '{{%cache}}';

        DbHelper::dropTable($db, $table);

        $this->assertNull($db->getTableSchema($table, true));

        $db->close();
    }
}
