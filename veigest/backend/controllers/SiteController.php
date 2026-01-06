<?php

namespace backend\controllers;

use common\models\LoginForm;
use common\models\User;
use common\models\Company;
use frontend\models\Vehicle;
use frontend\models\Maintenance;
use frontend\models\FuelLog;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;

/**
 * SiteController - Backend Main Controller
 * 
 * Access Control:
 * - Admin: FULL ACCESS
 * - Manager: LIMITED ACCESS (dashboard, tickets)
 * - Driver: NO ACCESS (403 Forbidden)
 */
class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Public actions (login, error)
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    // Dashboard: admin and manager
                    [
                        'actions' => ['index', 'logout'],
                        'allow' => true,
                        'roles' => ['admin', 'manager'],
                    ],
                    // Other backend actions: admin only
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
                // Custom deny callback - use blank layout for 403 errors
                'denyCallback' => function ($rule, $action) {
                    $action->controller->layout = 'blank';
                    throw new ForbiddenHttpException(
                        'Não tem permissão para aceder ao backend. Apenas administradores e gestores podem aceder a esta área.'
                    );
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
                'layout' => 'blank', // Use blank layout for error pages
            ],
        ];
    }

    /**
     * Displays backend homepage.
     */
    public function actionIndex()
    {
        // Estatísticas do sistema
        $totalUsers = User::find()->count();
        $totalCompanies = Company::find()->count();
        $totalVehicles = Vehicle::find()->count();
        $activeVehicles = Vehicle::find()->where(['status' => 'active'])->count();
        $maintenanceAlerts = Maintenance::find()
            ->where(['status' => 'scheduled'])
            ->andWhere(['<', 'date', date('Y-m-d')])
            ->count();
        
        // Consumo de combustível últimos 6 meses
        $fuelData = FuelLog::find()
            ->select(['DATE(date) as date', 'SUM(liters) as total_liters', 'SUM(value) as total_cost'])
            ->where(['>=', 'date', date('Y-m-d', strtotime('-6 months'))])
            ->groupBy('DATE(date)')
            ->orderBy('date ASC')
            ->asArray()
            ->all();
        
        $fuelLabels = [];
        $fuelValues = [];
        foreach ($fuelData as $row) {
            $fuelLabels[] = date('d/m', strtotime($row['date']));
            $fuelValues[] = (float)$row['total_liters'];
        }
        
        // Distribuição de veículos por estado
        $vehiclesByStatus = Vehicle::find()
            ->select(['status', 'COUNT(*) as count'])
            ->groupBy('status')
            ->asArray()
            ->all();
        
        $statusLabels = [];
        $statusValues = [];
        foreach ($vehiclesByStatus as $row) {
            $statusLabels[] = ucfirst($row['status']);
            $statusValues[] = $row['count'];
        }
        
        return $this->render('index', [
            'totalUsers' => $totalUsers,
            'totalCompanies' => $totalCompanies,
            'totalVehicles' => $totalVehicles,
            'activeVehicles' => $activeVehicles,
            'maintenanceAlerts' => $maintenanceAlerts,
            'fuelLabels' => json_encode($fuelLabels),
            'fuelValues' => json_encode($fuelValues),
            'statusLabels' => json_encode($statusLabels),
            'statusValues' => json_encode($statusValues),
        ]);
    }

    /**
     * Login action.
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     */
    public function actionLogout()
    {
        // Clear session and identity
        Yii::$app->user->logout();
        
        // Get cookie domain for cross-subdomain clearing
        $serverHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $isLocalhost = strpos($serverHost, 'localhost') !== false || strpos($serverHost, '127.0.0.1') !== false;
        $cookieDomain = $isLocalhost ? '' : '.dryadlang.org';
        
        // Clear identity cookie
        $cookies = Yii::$app->response->cookies;
        $cookies->remove('_identity-frontend');
        
        // Also expire the cookie directly in case remove doesn't work across domains
        if ($cookieDomain) {
            setcookie('_identity-frontend', '', time() - 3600, '/', $cookieDomain);
            setcookie('PHPSESSID', '', time() - 3600, '/', $cookieDomain);
            setcookie('_csrf-frontend', '', time() - 3600, '/', $cookieDomain);
            setcookie('_csrf-backend', '', time() - 3600, '/', $cookieDomain);
        } else {
            setcookie('_identity-frontend', '', time() - 3600, '/');
            setcookie('PHPSESSID', '', time() - 3600, '/');
            setcookie('_csrf-frontend', '', time() - 3600, '/');
            setcookie('_csrf-backend', '', time() - 3600, '/');
        }
        
        // Destroy session completely
        Yii::$app->session->destroy();

        return $this->redirect(['site/login']);
    }
}
