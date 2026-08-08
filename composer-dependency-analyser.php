<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;

return (new Configuration())
    ->disableComposerAutoloadPathScan()
    ->setFileExtensions(['php'])
    ->addPathToScan(__DIR__ . '/src', isDev: false)
    ->addPathToScan(__DIR__ . '/tests', isDev: true)
    // These classes come from DBMS-specific packages (yiisoft/db-mssql, yiisoft/db-mysql, yiisoft/db-oracle,
    // yiisoft/db-pgsql, yiisoft/db-sqlite) that are intentionally not required by this package: each is
    // installed on demand by the corresponding CI workflow (see .github/workflows/{mssql,mysql,oracle,pgsql,sqlite}.yml)
    // to run the test suite for that specific DBMS.
    ->ignoreUnknownClasses([
        'Yiisoft\Db\Mssql\Connection',
        'Yiisoft\Db\Mssql\Driver',
        'Yiisoft\Db\Mssql\Dsn',
        'Yiisoft\Db\Mysql\Connection',
        'Yiisoft\Db\Mysql\Driver',
        'Yiisoft\Db\Mysql\Dsn',
        'Yiisoft\Db\Oracle\Connection',
        'Yiisoft\Db\Oracle\Driver',
        'Yiisoft\Db\Oracle\Dsn',
        'Yiisoft\Db\Pgsql\Connection',
        'Yiisoft\Db\Pgsql\Driver',
        'Yiisoft\Db\Pgsql\Dsn',
        'Yiisoft\Db\Sqlite\Connection',
        'Yiisoft\Db\Sqlite\Driver',
        'Yiisoft\Db\Sqlite\Dsn',
    ]);
