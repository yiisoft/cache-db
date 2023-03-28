<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Yii Cache Library - DB Handler</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/cache-db/v/stable.png)](https://packagist.org/packages/yiisoft/cache-db)
[![Total Downloads](https://poser.pugx.org/yiisoft/cache-db/downloads.png)](https://packagist.org/packages/yiisoft/cache-db)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/cache-db/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/cache-db/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/cache-db/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/cache-db/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fcache-db%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/cache-db/master)
[![static analysis](https://github.com/yiisoft/cache-db/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/cache-db/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/cache-db/coverage.svg)](https://shepherd.dev/github/yiisoft/cache-db)

This package implements database-based [PSR-16](https://www.php-fig.org/psr/psr-16/) cache.

## Support databases:

|                      Packages                       |      PHP      |    Versions     |                                                                        CI-Actions                                                                         |
|:---------------------------------------------------:|:-------------:|:---------------:|:---------------------------------------------------------------------------------------------------------------------------------------------------------:|
|  [[db-mssql]](https://github.com/yiisoft/db-mssql)  | **8.0 - 8.1** | **2017 - 2019** |  [![mssql](https://github.com/yiisoft/cache-db/actions/workflows/mssql.yml/badge.svg)](https://github.com/yiisoft/cache-db/actions/workflows/mssql.yml)   | |
|  [[db-mysql]](https://github.com/yiisoft/db-mysql)  | **8.0 - 8.1** |  **5.7 - 8.0**  |  [![mysql](https://github.com/yiisoft/cache-db/actions/workflows/mysql.yml/badge.svg)](https://github.com/yiisoft/cache-db/actions/workflows/mysql.yml)   |
| [[db-oracle]](https://github.com/yiisoft/db-oracle) | **8.0 - 8.1** |  **11c - 21c**  | [![oracle](https://github.com/yiisoft/cache-db/actions/workflows/oracle.yml/badge.svg)](https://github.com/yiisoft/cache-db/actions/workflows/oracle.yml) |
|  [[db-pgsql]](https://github.com/yiisoft/db-pgsql)  | **8.0 - 8.1** | **9.0 - 15.0**  |  [![pgsql](https://github.com/yiisoft/cache-db/actions/workflows/pgsql.yml/badge.svg)](https://github.com/yiisoft/cache-db/actions/workflows/pgsql.yml)   |
| [[db-sqlite]](https://github.com/yiisoft/db-sqlite) | **8,0 - 8.1** |  **3:latest**   | [![sqlite](https://github.com/yiisoft/cache-db/actions/workflows/sqlite.yml/badge.svg)](https://github.com/yiisoft/cache-db/actions/workflows/sqlite.yml) |

## Requirements

- PHP 8.0 or higher.
- `PDO` PHP extension.

## Installation

The package could be installed with composer:

```
composer require yiisoft/cache-db --prefer-dist
```

## Configuration

When creating an instance of `\Yiisoft\Cache\Db\DbCache`, you must pass an instance of the database connection:

```php
$cache = new \Yiisoft\Cache\Db\DbCache($db, $table, $gcProbability);
```

- `$db (\Yiisoft\Db\Connection\ConnectionInterface)` - The database connection instance.
- `$table (string)` - The name of the database table to store the cache data. Defaults to "cache".
- `$gcProbability (int)` - The probability (parts per million) that garbage collection (GC) should
  be performed when storing a piece of data in the cache. Defaults to 100, meaning 0.01% chance.
  This number should be between 0 and 1000000. A value 0 meaning no GC will be performed at all.

## General usage

The package does not contain any additional functionality for interacting with the cache,
except those defined in the [PSR-16](https://www.php-fig.org/psr/psr-16/) interface.

```php
$cache = new \Yiisoft\Cache\Db\DbCache($db);
$parameters = ['user_id' => 42];
$key = 'demo';

// try retrieving $data from cache
$data = $cache->get($key);

if ($data === null) {
    // $data is not found in cache, calculate it from scratch
    $data = calculateData($parameters);
    
    // store $data in cache for an hour so that it can be retrieved next time
    $cache->set($key, $data, 3600);
}

// $data is available here
```

In order to delete value you can use:

```php
$cache->delete($key);
// Or all cache
$cache->clear();
```

To work with values in a more efficient manner, batch operations should be used:

- `getMultiple()`
- `setMultiple()`
- `deleteMultiple()`

This package can be used as a cache handler for the [Yii Caching Library](https://github.com/yiisoft/cache).

## Additional logging

In order to log details about failures you may set a logger instance. It should be `Psr\Log\LoggerInterface::class`. For example, you can use [yiisoft\Log](https://github.com/yiisoft/log):

```php
$cache = new \Yiisoft\Cache\Db\DbCache($db, $table, $gcProbability);
$cache->setLogger(new \Yiisoft\Log\Logger());
```

This allows you to log cache operations, when ocurring errors, etc.

## Testing

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework with
[Infection Static Analysis Plugin](https://github.com/Roave/infection-static-analysis-plugin). To run it:

```shell
./vendor/bin/roave-infection-static-analysis-plugin
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Yii Cache Library - DB Handler is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
