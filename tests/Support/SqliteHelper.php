<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection;
use Yiisoft\Db\Sqlite\Driver;

final class SqliteHelper extends ConnectionHelper
{
    private string $dsn = 'sqlite:' . __DIR__ . '/../runtime/test.sq3';
    private string $charset = 'UTF8MB4';

    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver($this->dsn, '', '');
        $pdoDriver->charset($this->charset);

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
