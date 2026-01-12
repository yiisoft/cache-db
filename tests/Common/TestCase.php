<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use ReflectionClass;
use ReflectionObject;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\DbSchemaManager;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Logger;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ConnectionInterface $db;
    protected DbCache $dbCache;
    protected ?Logger $logger = null;
    protected string $table = '{{%yii_cache}}';
    protected DbSchemaManager $dbSchemaManager;

    protected function setup(): void
    {
        // create db cache
        $this->dbCache = new DbCache($this->db, gcProbability: 1_000_000);

        // create db schema manager
        $this->dbSchemaManager = new DbSchemaManager($this->db);

        // create table
        $this->dbSchemaManager->ensureTable();
    }

    protected function tearDown(): void
    {
        // drop table
        $this->dbSchemaManager->ensureNoTable();

        // close db connection
        $this->db->close();

        unset($this->db, $this->dbCache, $this->dbSchemaManager, $this->logger);
    }

    protected function getLogger(): Logger
    {
        if ($this->logger === null) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    /**
     * Gets an inaccessible object property.
     */
    protected function getInaccessibleProperty(object $object, string $propertyName): mixed
    {
        $class = new ReflectionClass($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);

        /** @psalm-var mixed $result */
        return $property->getValue($object);
    }

    /**
     * Invokes an inaccessible method.
     *
     * @param object $object The object to invoke the method on.
     * @param string $method The name of the method to invoke.
     * @param array $args The arguments to pass to the method.
     */
    protected function invokeMethod(object $object, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionObject($object);
        $method = $reflection->getMethod($method);

        return $method->invokeArgs($object, $args);
    }
}
