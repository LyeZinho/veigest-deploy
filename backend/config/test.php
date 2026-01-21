<?php
return [
    'id' => 'app-backend-tests',
    'components' => [
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'csrfCookie' => [
                'path' => '/',
                'domain' => '',
                'httpOnly' => true,
                'secure' => false,
            ],
        ],
        'session' => [
            'class' => 'yii\web\Session',
            'cookieParams' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
                'httponly' => true,
            ],
        ],
        'user' => [
            'identityCookie' => [
                'name' => '_identity-test',
                'httpOnly' => true,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
        ],
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=veigest_db',
            'username' => 'veigest_user',
            'password' => 'secret',
            'charset' => 'utf8mb4',
        ],
    ],
];
