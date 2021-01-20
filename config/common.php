<?php

declare(strict_types=1);

use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Cache\Db\DbCache;

return [
    DbCache::class => static fn (ConnectionInterface $db) => new DbCache($db),
];
