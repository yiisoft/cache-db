<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use Yiisoft\Cache\Db\Migration;

abstract class AbstractDbHelperTest extends TestCase
{
    public function testDropTable(): void
    {
        $table = '{{%cache}}';

        Migration::dropTable($this->db, $table);

        $this->assertNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTable(): void
    {
        $table = '{{%cache}}';

        Migration::dropTable($this->db, '{{%cache}}');

        $this->assertNull($this->db->getTableSchema($table, true));

        Migration::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table, true));
    }

    public function testEnsureTableExist(): void
    {
        $table = '{{%cache}}';

        Migration::dropTable($this->db, '{{%cache}}');

        $this->assertNull($this->db->getTableSchema($table, true));

        Migration::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table));

        Migration::ensureTable($this->db, $table);

        $this->assertNotNull($this->db->getTableSchema($table));
    }

    public function testPrefixTable(): void
    {
        $this->assertSame('cache', $this->db->getSchema()->getRawTableName('{{%cache}}'));
    }
}
