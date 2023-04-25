<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use Yiisoft\Cache\Db\DbHelper;

abstract class AbstractDbHelperTest extends TestCase
{
    public function testDropTable(): void
    {
        $table = '{{%cache}}';

        DbHelper::dropTable($this->db, $table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTable(): void
    {
        $table = '{{%cache}}';

        DbHelper::dropTable($this->db, '{{%cache}}');

        $this->assertNull($this->db->getTableSchema($table, true));

        DbHelper::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTableExist(): void
    {
        $table = '{{%cache}}';

        DbHelper::dropTable($this->db, '{{%cache}}');

        $this->assertNull($this->db->getTableSchema($table, true));

        DbHelper::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table));

        DbHelper::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table));
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('cache', $this->db->getSchema()->getRawTableName('{{%cache}}'));
    }
}
