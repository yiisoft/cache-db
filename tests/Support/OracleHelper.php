<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use PDO;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Oracle\Connection;
use Yiisoft\Db\Oracle\Driver;

final class OracleHelper extends ConnectionHelper
{
    private string $dsn = 'oci:dbname=localhost/XE;';
    private string $username = 'system';
    private string $password = 'root';
    private string $charset = 'AL32UTF8';

    public function createConnection(bool $reset = true): ConnectionInterface
    {
        $pdoDriver = new Driver($this->dsn, $this->username, $this->password);
        $pdoDriver->charset($this->charset);
        $pdoDriver->attributes([PDO::ATTR_STRINGIFY_FETCHES => true]);

        $db = new Connection($pdoDriver, $this->createSchemaCache());

        if ($reset) {
            DbHelper::loadFixture($db, dirname(__DIR__, 2) . '/src/Migration/schema-oci.sql');
        }

        return $db;
    }
}
