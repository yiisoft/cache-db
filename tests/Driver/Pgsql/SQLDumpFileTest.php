<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Throwable;
use Yiisoft\Cache\Db\Tests\Common\AbstractSQLDumpFileTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlFactory;

/**
 * @group Pgsql
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
        $this->db = (new PgsqlFactory())->createConnection();

        parent::setUp();
    }
}
