<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Oracle;

use Throwable;
use Yiisoft\Cache\Db\Tests\Common\AbstractSQLDumpFileTest;
use Yiisoft\Cache\Db\Tests\Support\OracleFactory;

/**
 * @group Oracle
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class SQLDumpFileTest extends AbstractSQLDumpFileTest
{
    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new OracleFactory())->createConnection();

        parent::setUp();
    }
}
