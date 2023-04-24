<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Mysql\Dsn;
use Yiisoft\Db\Mysql\Connection;
use Yiisoft\Db\Mysql\Driver;

final class MysqlHelper extends ConnectionHelper
{
    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver(
            (new Dsn('mysql', '127.0.0.1', 'yiitest', '3306', ['charset' => 'utf8mb4']))->asString(),
            'root',
            '',
        );

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
