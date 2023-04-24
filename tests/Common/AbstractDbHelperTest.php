<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use Yiisoft\Cache\Db\DbHelper;

abstract class AbstractDbHelperTest extends TestCase
{
    public function testEnsureTable(): void
    {
        $table = '{{%cache}}';

        DbHelper::dropTable($this->db, '{{%cache}}');

        $this->assertNull($this->db->getTableSchema($table, true));
        $this->assertTrue(DbHelper::ensureTable($this->db, $table));
    }

    public function testDropTable(): void
    {
        $table = '{{%cache}}';

        DbHelper::dropTable($this->db, $table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    public function testDroptTableWithForce(): void
    {
        $table = '{{%cache}}';

        DbHelper::dropTable($this->db, $table, true);

        $this->assertNull($this->db->getTableSchema($table, true));
    }
}
