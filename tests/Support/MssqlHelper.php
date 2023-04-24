<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Mssql\Connection;
use Yiisoft\Db\Mssql\Driver;
use Yiisoft\Db\Mssql\Dsn;

final class MssqlHelper extends ConnectionHelper
{
    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver(
            (new Dsn('sqlsrv', 'localhost', 'yiitest'))->asString(),
            'SA',
            'YourStrong!Passw0rd',
        );

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
