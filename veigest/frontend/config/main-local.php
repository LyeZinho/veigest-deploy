<?php

// Detect if running on localhost or production domain
$isLocalhost = isset($_SERVER['HTTP_HOST']) && (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
);
$cookieDomain = $isLocalhost ? '' : '.dryadlang.org';

$config = [
    'components' => [
        'request' => [
            // Cookie validation key - must match between frontend and backend for shared sessions
            'cookieValidationKey' => 'Yup8MeyEmKivPSYV944gTuoRjBGqKkVt',
            'csrfCookie' => [
                'path' => '/',
                'domain' => $cookieDomain ?: null, // Share cookies across subdomains (null for localhost)
                'httpOnly' => true,
                'secure' => false, // Set to true in production with HTTPS
                'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
            ],
        ],
        'session' => [
            'cookieParams' => [
                'path' => '/',
                'domain' => $cookieDomain ?: null, // Share session across subdomains (null for localhost)
                'httpOnly' => true,
                'secure' => false, // Set to true in production with HTTPS
                'sameSite' => 'Lax',
            ],
        ],
        'user' => [
            'identityCookie' => [
                'name' => '_identity-frontend',
                'httpOnly' => true,
                'path' => '/',
                'domain' => $cookieDomain ?: null, // Share identity across subdomains (null for localhost)
                'secure' => false, // Set to true in production with HTTPS
                'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
            ],
        ],
    ],
];

if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
}

return $config;
