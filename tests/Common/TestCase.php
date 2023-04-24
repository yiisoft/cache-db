<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Common;

use ReflectionClass;
use ReflectionObject;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Log\Logger;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ConnectionInterface $db;
    protected DbCache $dbCache;
    protected Logger|null $logger = null;

    /**
     * Loads the fixture into the database.
     *
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected static function createMigrationFromSqlDump(ConnectionInterface $db, string $fixture): void
    {
        $db->open();

        if (
            $db->getDriverName() === 'oci' &&
            ($statments = explode('/* STATEMENTS */', file_get_contents($fixture), 2)) &&
            count($statments) === 2
        ) {
            [$drops, $creates] = $statments;
            $lines = array_merge(explode('--', $drops), explode(';', $creates));
        } else {
            $lines = explode(';', file_get_contents($fixture));
        }

        foreach ($lines as $line) {
            $db->createCommand(trim($line))->execute();
        }
    }

    protected function tearDown(): void
    {
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
