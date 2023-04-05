<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use PDO;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Oracle\Connection;
use Yiisoft\Db\Oracle\Driver;

final class OracleHelper extends ConnectionHelper
{
    private string $drivername = 'oci';
    private string $dsn = 'oci:dbname=localhost/XE;';
    private string $username = 'system';
    private string $password = 'oracle';
    private string $charset = 'AL32UTF8';

    public function createConnection(): ConnectionInterface
    {
        $pdoDriver = new Driver($this->dsn, $this->username, $this->password);
        $pdoDriver->charset($this->charset);
        $pdoDriver->attributes([PDO::ATTR_STRINGIFY_FETCHES => true]);

        return new Connection($pdoDriver, $this->createSchemaCache());
    }
}
