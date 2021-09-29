<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Factory\DatabaseFactory;
use Yiisoft\Db\Sqlite\Connection as SqlLiteConnection;
use Yiisoft\Definitions\Reference;
use Yiisoft\Di\Container;
use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
use Yiisoft\EventDispatcher\Provider\Provider;
use Yiisoft\Profiler\Profiler;
use Yiisoft\Profiler\ProfilerInterface;
use Yiisoft\Yii\Db\Migration\Informer\MigrationInformerInterface;
use Yiisoft\Yii\Db\Migration\Informer\NullMigrationInformer;

use function dirname;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected const DB_FILE = __DIR__ . '/runtime/test.sq3';

    private ?Container $container = null;

    protected function tearDown(): void
    {
        $db = $this->getContainer()->get(ConnectionInterface::class);

        foreach ($db->getSchema()->getTableNames() as $tableName) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        unset($this->container);
    }

    protected function getContainer(): Container
    {
        if ($this->container === null) {
            $this->container = new Container([
                Aliases::class => [
                    "class" => Aliases::class,
                    "__construct()" => [
                        '@root' => dirname(__DIR__, 2),
                        '@runtime' => __DIR__ . '/runtime',
                        '@yiisoft/yii/db/migration' => '@root',
                    ],
                ],

                CacheInterface::class => [
                    'class' => Cache::class,
                    '__construct()' => [
                        Reference::to(ArrayCache::class),
                    ],
                ],

                DbCache::class => static function (ContainerInterface $container) {
                    return new DbCache($container->get(ConnectionInterface::class), 'test-table');
                },

                ConnectionInterface::class => [
                    'class' => SqlLiteConnection::class,
                    '__construct()' => [
                        'dsn' => 'sqlite:' . self::DB_FILE,
                    ],
                    'setEnableProfiling()' => [false],
                ],

                MigrationInformerInterface::class => NullMigrationInformer::class,
                EventDispatcherInterface::class => Dispatcher::class,
                ListenerProviderInterface::class => Provider::class,
                LoggerInterface::class => NullLogger::class,
                ProfilerInterface::class => Profiler::class,
                PsrCacheInterface::class => DbCache::class,
            ]);
        }

        DatabaseFactory::initialize($this->container, []);

        return $this->container;
    }
}
