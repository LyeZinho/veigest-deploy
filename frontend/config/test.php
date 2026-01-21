<?php
return [
    'id' => 'app-frontend-tests',
    'bootstrap' => [\frontend\components\TestDbAligner::class],
    'homeUrl' => 'site/index',
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
            'enablePrettyUrl' => false,
            'rules' => [
                'index-test.php' => 'site/index',
                '/index-test.php' => 'site/index',
            ],
        ],
        'request' => [
            'cookieValidationKey' => 'test',
        ],
        'mailer' => [
            'messageClass' => \yii\symfonymailer\Message::class
        ],
        'testDbAligner' => [
            'class' => \frontend\components\TestDbAligner::class,
        ],
        'log' => [
            'traceLevel' => 3,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => __DIR__ . '/../runtime/logs/app.log',
                    'maxFileSize' => 10 * 1024 * 1024,
                    'maxLogFiles' => 5,
                ],
            ],
        ],
    ],
];
