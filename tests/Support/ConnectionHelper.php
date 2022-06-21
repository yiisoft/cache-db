<?php

declare(strict_types=1);

namespace Yiisoft\Cache\Db\Tests\Support;

use Yiisoft\Cache\ArrayCache;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Db\Cache\QueryCache;
use Yiisoft\Db\Cache\SchemaCache;

abstract class ConnectionHelper
{
    protected function createCache(): CacheInterface
    {
        return new Cache(new ArrayCache());
    }

    protected function createQueryCache(): QueryCache
    {
        return new QueryCache($this->createCache());
    }

    protected function createSchemaCache(): SchemaCache
    {
        return new SchemaCache($this->createCache());
    }
}
