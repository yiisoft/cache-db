<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Config\ConfigInterface;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Log\Logger;
use Yiisoft\Yii\Console\CommandLoader;
use Yiisoft\Yii\Runner\Console\ConsoleApplicationRunner;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ConnectionInterface $db;
    protected DbCache $dbCache;
    protected Logger|null $logger = null;

    protected function createMigration(ConnectionInterface $db, bool $force = false): int
    {
        $runner = new ConsoleApplicationRunner(
            rootPath: dirname(__DIR__),
            debug: false,
            checkEvents: false,
            paramsGroup: 'params',
        );

        $config = $runner->getConfig();
        $definitions = array_merge(
            $config->get('di'),
            [
                ConnectionInterface::class => $db,
                DbCache::class => [
                    '__construct()' => [
                        'table' => 'test-table',
                    ],
                ],
            ],
        );
        $containerConfig = ContainerConfig::create()->withDefinitions(array_merge($definitions));
        $container = new Container($containerConfig);
        $runner = $runner->withContainer($container);
        $container = $runner->getContainer();

        $this->dbCache = $container->get(DbCache::class);

        $app = new Application();

        $params = $config->get('params');

        $loader = new CommandLoader($container, $params['yiisoft/yii-console']['commands']);

        $app->setCommandLoader($loader);
        $command = $app->find('cache/migrate');

        $commandCreate = new CommandTester($command);

        return match ($force) {
            true => $commandCreate->execute(['--force' => true]),
            default => $commandCreate->execute([]),
        };
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
