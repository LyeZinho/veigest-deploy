<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],

    'as access' => [
    'class' => \yii\filters\AccessControl::class,
    'ruleConfig' => [
        'class' => \yii\filters\AccessRule::class,
    ],
    'except' => ['site/error', 'site/login', 'api/*'], // Excluir todas as rotas da API
    'rules' => [

        [
            'allow' => true,
            'actions' => ['login', 'error'], 
        ],

        // Backend access: admin only (check user role directly)
        [
            'allow' => true,
            'roles' => ['@'], // Must be logged in
            'matchCallback' => function ($rule, $action) {
                $user = Yii::$app->user->identity;
                return $user && $user->role === 'admin';
            },
        ],
    ],
    'denyCallback' => function ($rule, $action) {
        // If not logged in, redirect to login
        if (Yii::$app->user->isGuest) {
            return Yii::$app->response->redirect(['site/login']);
        }
        // If logged in but not admin, show 403
        $action->controller->layout = 'blank';
        throw new \yii\web\ForbiddenHttpException(
            'You do not have permission to access the backend. Only administrators can access this area.'
        );
    },
],


    'modules' => [
        'api' => [
            'class' => 'backend\modules\api\Module',
        ],
    ],
    'components' => [

        'errorHandler' => [
            'errorAction' => 'site/error',
    ],
        'request' => [
            'csrfParam' => '_csrf',
            'cookieValidationKey' => 'Yup8MeyEmKivPSYV944gTuoRjBGqKkVt',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],

        // Sessão compartilhada com frontend (mesmo domínio)
        'session' => [
            'class' => 'yii\web\Session',
            'name' => 'VeiGestSession',
            'cookieParams' => [
                'path' => '/',
                'domain' => '.dryadlang.org',
            ],
        ],

        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => [
                'name' => '_identity-frontend', // Same as frontend to share session
                'httpOnly' => true,
                'path' => '/',
                'domain' => '.dryadlang.org',
            ],
        ],

        
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],

        // urlManager para API do backend
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // Authentication endpoints
                'POST api/auth/login' => 'api/auth/login',
                'POST api/auth/logout' => 'api/auth/logout',
                'GET api/auth/me' => 'api/auth/me',
                'POST api/auth/refresh' => 'api/auth/refresh',
                
                // REST API routes
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/vehicle'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/user'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/company'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/maintenance'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/fuel-log'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/file'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/document'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/alert'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/activity-log'], 'pluralize' => false],
                ['class' => 'yii\\rest\\UrlRule', 'controller' => ['api/route'], 'pluralize' => false],

                
                // Custom endpoints for companies
                'GET api/companies/<id:\d+>/vehicles' => 'api/company/vehicles',
                'GET api/companies/<id:\d+>/users' => 'api/company/users',
                'GET api/companies/<id:\d+>/stats' => 'api/company/stats',
                
                // Custom endpoints for vehicles  
                'GET api/vehicles/<id:\d+>/maintenances' => 'api/vehicle/maintenances',
                'GET api/vehicles/<id:\d+>/fuel-logs' => 'api/vehicle/fuel-logs',
                'GET api/vehicles/<id:\d+>/stats' => 'api/vehicle/stats',
                'GET api/vehicles/by-status/<status:\w+>' => 'api/vehicle/by-status',
                
                // Custom endpoints for maintenance
                'GET api/maintenance/by-vehicle/<vehicle_id:\d+>' => 'api/maintenance/by-vehicle',
                'GET api/maintenance/by-status/<estado:\w+>' => 'api/maintenance/by-status',
                'POST api/maintenance/<id:\d+>/schedule' => 'api/maintenance/schedule',
                'GET api/maintenance/reports/monthly' => 'api/maintenance/reports-monthly',
                'GET api/maintenance/reports/costs' => 'api/maintenance/reports-costs',
                'GET api/maintenance/stats' => 'api/maintenance/stats',
                
                // Custom endpoints for fuel logs
                'GET api/fuel-logs/by-vehicle/<vehicle_id:\d+>' => 'api/fuel-log/by-vehicle',
                'GET api/fuel-logs/stats' => 'api/fuel-log/stats',
                'GET api/fuel-logs/alerts' => 'api/fuel-log/alerts',
                'GET api/fuel-logs/efficiency-report' => 'api/fuel-log/efficiency-report',
                
                // Custom endpoints for users
                'GET api/users/drivers' => 'api/user/drivers',
                'GET api/users/profile' => 'api/user/profile',
                'GET api/users/by-company/<company_id:\d+>' => 'api/user/by-company',
                'POST api/users/<id:\d+>/update-photo' => 'api/user/update-photo',
                
                // Custom endpoints for files
                'POST api/files/upload' => 'api/file/upload',
                'GET api/files/stats' => 'api/file/stats',
                
                // Custom endpoints for documents
                'GET api/documents/by-vehicle/<vehicle_id:\d+>' => 'api/document/by-vehicle',
                'GET api/documents/by-driver/<driver_id:\d+>' => 'api/document/by-driver',
                'GET api/documents/expiring' => 'api/document/expiring',
                'GET api/documents/expired' => 'api/document/expired',
                'GET api/documents/stats' => 'api/document/stats',
                'GET api/documents/types' => 'api/document/types',
                
                // Custom endpoints for alerts
                'POST api/alerts/<id:\d+>/resolve' => 'api/alert/resolve',
                'POST api/alerts/<id:\d+>/ignore' => 'api/alert/ignore',
                'POST api/alerts/<id:\d+>/broadcast' => 'api/alert/broadcast',
                'GET api/alerts/mqtt-info' => 'api/alert/mqtt-info',
                'GET api/alerts/by-type/<type:\w+>' => 'api/alert/by-type',
                'GET api/alerts/by-priority/<priority:\w+>' => 'api/alert/by-priority',
                'GET api/alerts/count' => 'api/alert/count',
                'GET api/alerts/stats' => 'api/alert/stats',
                'GET api/alerts/types' => 'api/alert/types',
                'GET api/alerts/priorities' => 'api/alert/priorities',
                'POST api/alerts/bulk-resolve' => 'api/alert/bulk-resolve',
                
                // Custom endpoints for activity logs
                'GET api/activity-logs/by-user/<user_id:\d+>' => 'api/activity-log/by-user',
                'GET api/activity-logs/by-entity/<entity:\w+>/<entity_id:\d+>' => 'api/activity-log/by-entity',
                'GET api/activity-logs/recent' => 'api/activity-log/recent',
                'GET api/activity-logs/stats' => 'api/activity-log/stats',
                'GET api/activity-logs/actions' => 'api/activity-log/actions',
                'GET api/activity-logs/entities' => 'api/activity-log/entities',
                
                // Custom endpoints for routes
                'POST api/routes/<id:\d+>/complete' => 'api/route/complete',
                'GET api/routes/by-vehicle/<vehicle_id:\d+>' => 'api/route/by-vehicle',
                'GET api/routes/by-driver/<driver_id:\d+>' => 'api/route/by-driver',
                'GET api/routes/active' => 'api/route/active',
                'GET api/routes/scheduled' => 'api/route/scheduled',
                'GET api/routes/stats' => 'api/route/stats',
            ],
        ],
    ],
    'params' => $params,
];
