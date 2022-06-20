<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\ConnectionPDO;
use Yiisoft\Db\Sqlite\PDODriver;

final class SqliteHelper extends ConnectionHelper
{
    private string $drivername = 'sqlite';
    private string $dsn = 'sqlite:' . __DIR__ . '/../runtime/test.sq3';
    private string $charset = 'UTF8MB4';

    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new PDODriver($this->dsn, '', '');
        $pdoDriver->charset($this->charset);

        return new ConnectionPDO($pdoDriver, $this->createQueryCache(), $this->createSchemaCache());
    }
}
