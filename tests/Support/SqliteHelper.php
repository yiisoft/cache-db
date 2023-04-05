<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\PdoConnection;
use Yiisoft\Db\Sqlite\PdoDriver;

final class SqliteHelper extends ConnectionHelper
{
    private string $drivername = 'sqlite';
    private string $dsn = 'sqlite:' . __DIR__ . '/../runtime/test.sq3';
    private string $charset = 'UTF8MB4';

    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new PdoDriver($this->dsn, '', '');
        $pdoDriver->charset($this->charset);

        return new PdoConnection($pdoDriver, $this->createSchemaCache());
    }
}
