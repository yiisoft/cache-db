<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests;

use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Log\LoggerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Cache\Db\DbCache;
use Yiisoft\Db\Cache\SchemaCache;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Sqlite\Connection as SqlLiteConnection;
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
                    '@root' => dirname(__DIR__, 2),
                    '@runtime' => __DIR__ . '/runtime',
                    '@yiisoft/yii/db/migration' => '@root',
                ],

                DbCache::class => static function (ContainerInterface $container) {
                    return new DbCache($container->get(ConnectionInterface::class), 'test-table');
                },

                ConnectionInterface::class => [
                    '__class' => SqlLiteConnection::class,
                    '__construct()' => [
                        'dsn' => 'sqlite:' . self::DB_FILE,
                    ],
                    'setEnableProfiling()' => [false],
                ],

                // TEMPORARILY
                SchemaCache::class => static function (CacheInterface $cache) {
                    $schemaCache = new SchemaCache($cache);
                    $schemaCache->setEnable(false);
                    return $schemaCache;
                },

                MigrationInformerInterface::class => NullMigrationInformer::class,
                EventDispatcherInterface::class => Dispatcher::class,
                ListenerProviderInterface::class => Provider::class,
                LoggerInterface::class => NullLogger::class,
                ProfilerInterface::class => Profiler::class,
                PsrCacheInterface::class => DbCache::class,
                CacheInterface::class => Cache::class,
            ]);
        }

        return $this->container;
    }
}
