<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use ReflectionClass;
use ReflectionObject;
use Throwable;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Cache\Db\Migration;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Exception\InvalidConfigException;
use Yiisoft\Log\Logger;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ConnectionInterface $db;
    protected DbCache $dbCache;
    protected Logger|null $logger = null;
    protected string $table = '{{%cache}}';

    protected function setup(): void
    {
        // create db cache
        $this->dbCache = new DbCache($this->db, gcProbability: 1_000_000);
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    protected function tearDown(): void
    {
        // drop table
        Migration::dropTable($this->db);

        $this->db->close();

        unset($this->db, $this->dbCache, $this->logger);
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
     *
     * @param bool $revoke whether to make property inaccessible after getting.
     */
    protected function getInaccessibleProperty(object $object, string $propertyName, bool $revoke = true): mixed
    {
        $class = new ReflectionClass($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);

        $property->setAccessible(true);

        /** @psalm-var mixed $result */
        $result = $property->getValue($object);

        if ($revoke) {
            $property->setAccessible(false);
        }

        return $result;
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
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
