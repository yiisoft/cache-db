<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Pgsql;

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

        /** @var ConnectionInterface */
        $this->db = (new PgsqlHelper())->createConnection();

        // create cache instance
        $this->dbCache = $this->createDbCache();
    }
}
