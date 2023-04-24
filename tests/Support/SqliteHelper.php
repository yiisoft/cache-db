<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;
use Yiisoft\Db\Sqlite\Dsn;

final class SqliteHelper extends ConnectionHelper
{
    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver((new Dsn('sqlite', __DIR__ . '/runtime/yiitest.sq3'))->asString());

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
