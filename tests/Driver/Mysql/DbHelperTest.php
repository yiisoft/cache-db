<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Driver\Mysql;

use Yiisoft\Cache\Db\Tests\Common\AbstractDbHelperTest;
use Yiisoft\Cache\Db\Tests\Support\MysqlHelper;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;

/**
 * @group Mysql
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class DbHelperTest extends AbstractDbHelperTest
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function setUp(): void
    {
        // create connection dbms-specific
        $this->db = (new MysqlHelper())->createConnection();

        parent::setUp();
    }
}