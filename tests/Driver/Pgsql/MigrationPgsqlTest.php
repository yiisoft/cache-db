<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Yiisoft\Cache\Db\Tests\MigrationTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlHelper;

/**
 * @group Pgsql
 */
final class MigrationPgsqlTest extends MigrationTest
{
    protected function setUp(): void
    {
        parent::setUp();

        // create connection dbms-specific
        $this->db = (new PgsqlHelper())->createConnection();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->close();
    }
}
