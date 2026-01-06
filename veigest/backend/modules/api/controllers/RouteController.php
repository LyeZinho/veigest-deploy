<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\Route;
use backend\modules\api\models\Vehicle;
use common\models\User;

/**
 * Route API Controller
 * 
 * Fornece operações CRUD para rotas com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * .
 */
class RouteController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Route';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * List routes with filters
     * GET /api/routes
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        $query = Route::find()->where(['company_id' => $companyId]);

        // Filter by vehicle
        $vehicleId = Yii::$app->request->get('vehicle_id');
        if ($vehicleId) {
            $query->andWhere(['vehicle_id' => $vehicleId]);
        }

        // Filter by driver
        $driverId = Yii::$app->request->get('driver_id');
        if ($driverId) {
            $query->andWhere(['driver_id' => $driverId]);
        }

        // Filter by status
        $status = Yii::$app->request->get('status');
        if ($status) {
            switch ($status) {
                case 'scheduled':
                    $query->andWhere(['>', 'start_time', date('Y-m-d H:i:s')]);
                    break;
                case 'in_progress':
                    $query->andWhere(['<=', 'start_time', date('Y-m-d H:i:s')]);
                    $query->andWhere(['OR', ['end_time' => null], ['>', 'end_time', date('Y-m-d H:i:s')]]);
                    break;
                case 'completed':
                    $query->andWhere(['IS NOT', 'end_time', null]);
                    $query->andWhere(['<', 'end_time', date('Y-m-d H:i:s')]);
                    break;
            }
        }

        // Filter by date range
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        if ($startDate) {
            $query->andWhere(['>=', 'start_time', $startDate . ' 00:00:00']);
        }
        if ($endDate) {
            $query->andWhere(['<=', 'start_time', $endDate . ' 23:59:59']);
        }

        // Search by location
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['OR',
                ['LIKE', 'start_location', $search],
                ['LIKE', 'end_location', $search],
            ]);
        }

        // Order by
        $sort = Yii::$app->request->get('sort', '-start_time');
        if (strpos($sort, '-') === 0) {
            $query->orderBy([substr($sort, 1) => SORT_DESC]);
        } else {
            $query->orderBy([$sort => SORT_ASC]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * View a specific route
     * GET /api/routes/{id}
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }

    /**
     * Create a new route
     * POST /api/routes
     */
    public function actionCreate()
    {
        $model = new Route();
        $model->company_id = $this->getCompanyId();
        $model->load(Yii::$app->request->bodyParams, '');

        // Validate vehicle belongs to company
        if ($model->vehicle_id) {
            $vehicle = Vehicle::findOne(['id' => $model->vehicle_id, 'company_id' => $model->company_id]);
            if (!$vehicle) {
                return $this->errorResponse('Veículo não encontrado ou não pertence à sua empresa', 400);
            }
        }

        // Validate driver belongs to company
        if ($model->driver_id) {
            $driver = User::findOne(['id' => $model->driver_id, 'company_id' => $model->company_id]);
            if (!$driver) {
                return $this->errorResponse('Condutor não encontrado ou não pertence à sua empresa', 400);
            }
        }

        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);
            return $this->successResponse($model, 'Rota criada com sucesso');
        }

        return $this->errorResponse('Erro ao criar rota', 400, $model->errors);
    }

    /**
     * Update a route
     * PUT /api/routes/{id}
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->bodyParams, '');

        if ($model->save()) {
            return $this->successResponse($model, 'Rota atualizada com sucesso');
        }

        return $this->errorResponse('Erro ao atualizar rota', 400, $model->errors);
    }

    /**
     * Delete a route
     * DELETE /api/routes/{id}
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            return $this->successResponse(null, 'Rota excluída com sucesso');
        }

        return $this->errorResponse('Erro ao excluir rota', 500);
    }

    /**
     * Complete a route (set end time to now)
     * POST /api/routes/{id}/complete
     */
    public function actionComplete($id)
    {
        $model = $this->findModel($id);

        if ($model->end_time) {
            return $this->errorResponse('Esta rota já foi concluída', 400);
        }

        if ($model->complete()) {
            return $this->successResponse($model, 'Rota concluída com sucesso');
        }

        return $this->errorResponse('Erro ao concluir rota', 500);
    }

    /**
     * Get routes by vehicle
     * GET /api/routes/by-vehicle/{vehicle_id}
     */
    public function actionByVehicle($vehicle_id)
    {
        $companyId = $this->getCompanyId();

        // Verify vehicle belongs to company
        $vehicle = Vehicle::findOne(['id' => $vehicle_id, 'company_id' => $companyId]);
        if (!$vehicle) {
            throw new NotFoundHttpException('Veículo não encontrado.');
        }

        $query = Route::find()
            ->where(['company_id' => $companyId, 'vehicle_id' => $vehicle_id])
            ->orderBy(['start_time' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get routes by driver
     * GET /api/routes/by-driver/{driver_id}
     */
    public function actionByDriver($driver_id)
    {
        $companyId = $this->getCompanyId();

        $query = Route::find()
            ->where(['company_id' => $companyId, 'driver_id' => $driver_id])
            ->orderBy(['start_time' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get active routes (in progress)
     * GET /api/routes/active
     */
    public function actionActive()
    {
        $companyId = $this->getCompanyId();

        $query = Route::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['<=', 'start_time', date('Y-m-d H:i:s')])
            ->andWhere(['OR', ['end_time' => null], ['>', 'end_time', date('Y-m-d H:i:s')]])
            ->orderBy(['start_time' => SORT_ASC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get scheduled routes (future)
     * GET /api/routes/scheduled
     */
    public function actionScheduled()
    {
        $companyId = $this->getCompanyId();

        $query = Route::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>', 'start_time', date('Y-m-d H:i:s')])
            ->orderBy(['start_time' => SORT_ASC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get route statistics
     * GET /api/routes/stats
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();

        $totalRoutes = Route::find()->where(['company_id' => $companyId])->count();
        
        $completedRoutes = Route::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['IS NOT', 'end_time', null])
            ->andWhere(['<', 'end_time', date('Y-m-d H:i:s')])
            ->count();

        $activeRoutes = Route::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['<=', 'start_time', date('Y-m-d H:i:s')])
            ->andWhere(['OR', ['end_time' => null], ['>', 'end_time', date('Y-m-d H:i:s')]])
            ->count();

        $scheduledRoutes = Route::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>', 'start_time', date('Y-m-d H:i:s')])
            ->count();

        // Average duration (completed routes)
        $avgDuration = Yii::$app->db->createCommand("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_minutes
            FROM routes
            WHERE company_id = :company_id AND end_time IS NOT NULL
        ")->bindValue(':company_id', $companyId)->queryScalar();

        // Routes by driver (top 10)
        $byDriver = Yii::$app->db->createCommand("
            SELECT u.name as driver_name, COUNT(r.id) as count
            FROM routes r
            INNER JOIN users u ON r.driver_id = u.id
            WHERE r.company_id = :company_id
            GROUP BY r.driver_id
            ORDER BY count DESC
            LIMIT 10
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Routes by vehicle (top 10)
        $byVehicle = Yii::$app->db->createCommand("
            SELECT v.license_plate, COUNT(r.id) as count
            FROM routes r
            INNER JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.company_id = :company_id
            GROUP BY r.vehicle_id
            ORDER BY count DESC
            LIMIT 10
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Routes per day (last 30 days)
        $perDay = Yii::$app->db->createCommand("
            SELECT DATE(start_time) as date, COUNT(*) as count
            FROM routes
            WHERE company_id = :company_id
              AND start_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(start_time)
            ORDER BY date ASC
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Popular routes (locations)
        $popularRoutes = Yii::$app->db->createCommand("
            SELECT start_location, end_location, COUNT(*) as count
            FROM routes
            WHERE company_id = :company_id
            GROUP BY start_location, end_location
            ORDER BY count DESC
            LIMIT 10
        ")->bindValue(':company_id', $companyId)->queryAll();

        return $this->successResponse([
            'total_routes' => (int) $totalRoutes,
            'completed_routes' => (int) $completedRoutes,
            'active_routes' => (int) $activeRoutes,
            'scheduled_routes' => (int) $scheduledRoutes,
            'avg_duration_minutes' => $avgDuration ? round($avgDuration, 2) : null,
            'by_driver' => $byDriver,
            'by_vehicle' => $byVehicle,
            'per_day_last_30' => $perDay,
            'popular_routes' => $popularRoutes,
        ]);
    }

    /**
     * Find model with company check
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = Route::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Rota não encontrada.');
        }

        return $model;
    }
}
