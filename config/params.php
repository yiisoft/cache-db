<?php

declare(strict_types=1);

use Yiisoft\Cache\Db\Command\CreateCacheMigration;

return [
    // Yii console
    'yiisoft/yii-console' => [
        'commands' => [
            'cache/migrate' => CreateCacheMigration::class,
        ],
    ],
];
