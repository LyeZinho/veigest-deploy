<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Vehicle;
use frontend\models\Driver;
use frontend\models\Maintenance;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;

/**
 * Dashboard controller
 * 
 * Access Control:
 * - Admin: NO ACCESS (backend only)
 * - Manager: FULL ACCESS
 * - Driver: READ ONLY (limited menu items)
 */
class DashboardController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Block admin from frontend dashboard
                    [
                        'allow' => false,
                        'roles' => ['admin'],
                        'denyCallback' => function ($rule, $action) {
                            throw new ForbiddenHttpException(
                                'Administrators do not have access to the frontend. Please use the backend.'
                            );
                        },
                    ],
                    // Allow manager and driver
                    [
                        'allow' => true,
                        'roles' => ['manager', 'driver'],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->layout = 'dashboard';
    }

    /**
     * Displays dashboard homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Aqui você pode adicionar lógica para buscar dados do dashboard
        // Por exemplo: estatísticas, alertas recentes, etc.

        return $this->render('index');
    }

    /**
     * Displays alerts page.
     *
     * @return \yii\web\Response
     */
    public function actionAlerts()
    {
        return $this->redirect(['alert/index']);
    }

    /**
     * Displays documents page.
     * Redireciona para DocumentController::actionIndex()
     *
     * @return \yii\web\Response
     */
    public function actionDocuments()
    {
        return $this->redirect(['document/index']);
    }

    /**
     * Displays drivers page.
     * Redireciona para DriverController::actionIndex()
     *
     * @return \yii\web\Response
     */
    public function actionDrivers()
    {
        return $this->redirect(['driver/index']);
    }

    /**
     * Displays maintenance page.
     * Redireciona para MaintenanceController::actionIndex()
     *
     * @return \yii\web\Response
     */
    public function actionMaintenance($status = 'scheduled')
    {
        return $this->redirect(['maintenance/index', 'status' => $status]);
    }

    /**
     * Displays reports page.
     * Redireciona para ReportController::actionIndex()
     *
     * @return \yii\web\Response
     */
    public function actionReports()
    {
        return $this->redirect(['report/index']);
    }

    /**
     * Displays vehicles page.
     * Redireciona para VehicleController::actionIndex()
     *
     * @return \yii\web\Response
     */
    public function actionVehicles()
    {
        return $this->redirect(['vehicle/index']);
    }
}
