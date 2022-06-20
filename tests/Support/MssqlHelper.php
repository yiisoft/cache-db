<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Mssql\ConnectionPDO;
use Yiisoft\Db\Mssql\PDODriver;

final class MssqlHelper extends ConnectionHelper
{
    private string $drivername = 'sqlsrv';
    private string $dsn = 'sqlsrv:Server=127.0.0.1,1433;Database=yiitest';
    private string $username = 'SA';
    private string $password = 'YourStrong!Passw0rd';
    private string $charset = 'UTF8MB4';

    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new PDODriver($this->dsn, $this->username, $this->password);
        $pdoDriver->charset($this->charset);

        return new ConnectionPDO($pdoDriver, $this->createQueryCache(), $this->createSchemaCache());
    }
}
