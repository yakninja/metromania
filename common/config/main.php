<?php
return [
    'language' => 'ru',
    'sourceLanguage' => 'en-US',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'redis',
            'retries' => 1,
            'database' => 0,
        ],
        'queue' => [
            'class' => 'yii\queue\redis\Queue',
            'channel' => 'metromaniaQueue', // Queue channel key
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
        'mutex' => [
            'class' => 'yii\redis\Mutex',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/../messages',
                    'sourceLanguage' => 'en-US',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'error.php',
                    ],
                ]
            ],
        ],
        'formatter' => [
            'dateFormat' => 'php:Y-m-d',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
        ],
    ],
];
