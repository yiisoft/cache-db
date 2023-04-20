<?php

declare(strict_types=1);

// Do not edit. Content will be replaced.
return [
    '/' => [
        'di' => [
            'yiisoft/cache' => [
                'config/di.php',
            ],
            'yiisoft/yii-event' => [
                'config/di.php',
            ],
        ],
        'events-console' => [
            'yiisoft/log' => [
                'config/events-console.php',
            ],
            'yiisoft/yii-console' => [
                'config/events-console.php',
            ],
        ],
        'events-web' => [
            'yiisoft/log' => [
                'config/events-web.php',
            ],
        ],
        'params' => [
            'yiisoft/yii-console' => [
                'config/params.php',
            ],
            '/' => [
                'params.php',
            ],
        ],
        'di-console' => [
            'yiisoft/yii-console' => [
                'config/di-console.php',
            ],
            'yiisoft/yii-event' => [
                'config/di-console.php',
            ],
        ],
        'di-web' => [
            'yiisoft/yii-event' => [
                'config/di-web.php',
            ],
        ],
    ],
    'tests' => [
        'di' => [
            '/' => [
                'tests/Support/config/*.php',
            ],
        ],
    ],
];
