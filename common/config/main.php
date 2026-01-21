<?php
return [
    // aliases usados pelo projeto
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],

    // caminho para a vendor directory
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',

    'components' => [
        // cache padrão (FileCache)
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],

        // SESSÃO PARTILHADA entre frontend e backend
        // definimos aqui para que ambos os aplicativos usem a mesma sessão
        'session' => [
            'class' => 'yii\web\Session',
            // nome da cookie de sessão ( visível em todas as apps porque 'path' => '/' )
            'name' => 'VeiGestSession',
            'cookieParams' => [
                // essencial: disponibilizar cookie no root do domínio para partilha
                'path' => '/',
                // opcional: 'httponly' => true,
            ],
        ],

        // RBAC usando banco de dados (tabelas auth_*)
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            // você pode definir caches/itens adicionais aqui se desejar
        ],
    ],
];
