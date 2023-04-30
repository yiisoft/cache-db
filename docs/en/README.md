# Getting started

## Requirements

- The minimum version of PHP required by this package is `8.0`.
- `PDO` PHP extension.

## Installation

The package could be installed with composer:

```
composer require yiisoft/cache-db --prefer-dist
```

## Create Table

The package provides a `DbHelper::class` that creates the cache table for default `{{%cache}}`. You can use it as follows:

```php
DbHelper::ensureTable($db);
```

For custom table name you can use:

```php
DbHelper::ensureTable($db, '{{%custom_cache_table}}');
```

> Note: Additionally you can import the `RAW SQL` directly to create the tables.
>
>- [schema-mssql](/docs/en/migration/schema-mssql.sql).
>- [schema-mysql](/docs/en/migration/schema-mysql.sql).
>- [schema-oracle](/docs/en/migration/schema-oci.sql).
>- [schema-pgsql](/docs/en/migration/schema-pgsql.sql).
>- [schema-sqlite](/docs/en/migration/schema-sqlite.sql).

## Drop Table

For dropping table you can use:

```php
DbHelper::dropTable($db);
```

For custom table name you can use:

```php
DbHelper::dropTable($db, '{{%custom_cache_table}}');
```

## Configuration

When creating an instance of `\Yiisoft\Cache\Db\DbCache`, you must pass an instance of the database connection,
for more information see [yiisoft/db](https://github.com/yiisoft/db/tree/master/docs/en#create-connection).

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
