<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\OracleFactory;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheTest extends AbstractDbCacheTest
{
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleFactory())->createConnection();

        parent::setUp();
    }
}
