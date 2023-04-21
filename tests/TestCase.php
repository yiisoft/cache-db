<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use ReflectionClass;
use ReflectionObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Yiisoft\Cache\Db\Command\CreateCacheMigration;
use Yiisoft\Cache\Db\DbCache;
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
    protected CommandTester $commandTester;

    /**
     * Asserting two strings equality ignoring line endings.
     *
     * @param string $expected The expected string.
     * @param string $actual The actual string.
     * @param string $message The message to display if the assertion fails.
     */
    public function equalsWithoutLE(string $expected, string $actual, string $message = ''): void
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);

        $this->assertEquals($expected, $actual, $message);
    }

    protected function createApplication(ConnectionInterface $db): Application
    {
        $app = new Application();
        $command = new CreateCacheMigration(new DbCache($db, 'test-table'));
        $app->add($command);

        return $app;
    }

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
                        'table' => '{{%test-table}}',
                        'gcProbability' => 1,
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

        $this->commandTester = new CommandTester($command);

        return match ($force) {
            true => $this->commandTester->execute(['--force' => true]),
            default => $this->commandTester->execute([]),
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
