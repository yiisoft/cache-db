<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\OracleHelper;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationTest extends AbstractMigrationTest
{
    protected function setup(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleHelper())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('oci_');

        parent::setUp();
    }
}
