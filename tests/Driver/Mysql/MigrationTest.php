<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Migration;
use Yiisoft\Cache\Db\Tests\Common\AbstractMigrationTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MigrationTest extends AbstractMigrationTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setup(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();

        // create migration
        Migration::ensureTable($this->db);

        parent::setUp();
    }
}
