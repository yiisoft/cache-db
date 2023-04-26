<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Pgsql;

use Yiisoft\Cache\Db\Migration;
use Yiisoft\Cache\Db\Tests\Common\AbstractDbCacheTest;
use Yiisoft\Cache\Db\Tests\Support\PgsqlHelper;

/**
 * @group Pgsql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbCacheTest extends AbstractDbCacheTest
{
    protected string $tableWithPrefix = 'pgsql_cache';

    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new PgsqlHelper())->createConnection();

        // create migration
        Migration::ensureTable($this->db);

        parent::setUp();
    }
}
