<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Throwable;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbSchemaManagerTest;
use Yiisoft\Cache\Db\Tests\Support\OracleFactory;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbSchemaManagerTest extends AbstractDbSchemaManagerTest
{
    /**
     * @throws Throwable
     */
    protected function setup(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleFactory())->createConnection();

        // set table prefix
        $this->db->setTablePrefix('oci_');

        parent::setUp();
    }
}
