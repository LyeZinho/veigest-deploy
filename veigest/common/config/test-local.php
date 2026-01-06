<?php

return [
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=veigest_db',
            'username' => 'veigest_user',
            'password' => 'secret',
            'charset' => 'utf8mb4',
            'attributes' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
            ],
        ],
    ],
];
