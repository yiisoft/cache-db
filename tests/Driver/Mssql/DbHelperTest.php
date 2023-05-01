<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mssql;

use Yiisoft\Cache\Db\Tests\Common\AbstractDbHelperTest;
use Yiisoft\Cache\Db\Tests\Support\MssqlFactory;

/**
 * @group Mssql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbHelperTest extends AbstractDbHelperTest
{
    protected function setup(): void
    {
        // create connection dbms-specific
        $this->db = (new MssqlFactory())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('mssql_');

        parent::setUp();
    }
}
