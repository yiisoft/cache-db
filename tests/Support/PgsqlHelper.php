<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Pgsql\ConnectionPDO;
use Yiisoft\Db\Pgsql\PDODriver;

final class PgsqlHelper extends ConnectionHelper
{
    private string $drivername = 'pgsql';
    private string $dsn = 'pgsql:host=127.0.0.1;dbname=yiitest;port=5432';
    private string $username = 'root';
    private string $password = 'root';
    private string $charset = 'UTF8';

    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new PDODriver($this->dsn, $this->username, $this->password);
        $pdoDriver->setCharset($this->charset);

        return new ConnectionPDO($pdoDriver, $this->createQueryCache(), $this->createSchemaCache());
    }
}
