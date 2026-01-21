<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\Vehicle;

/**
 * Vehicle API Controller
 * 
 * Fornece operações CRUD para veículos com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * .
 */
class VehicleController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Vehicle';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        // Customizar a ação index para aplicar filtros de empresa
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

    /**
     * Lista todos os veículos da empresa do usuário
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Empresa não identificada no token');
        }

        $query = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->with(['company', 'maintenances', 'fuelLogs']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC]
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Cria novo veículo
     * Automaticamente associa à empresa do usuário
     * 
     * @return Vehicle
     */
    public function actionCreate()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Empresa não identificada no token');
        }

        $model = new Vehicle();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->company_id = $companyId; // Forçar company_id do token

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $this->successResponse($model, 'Veículo criado com sucesso', 201);
        }

        return $this->errorResponse('Erro ao criar veículo', 400, $model->errors);
    }

    /**
     * Atualiza veículo existente
     * Verifica se pertence à empresa do usuário
     * 
     * @param integer $id
     * @return Vehicle
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $model->load(Yii::$app->request->bodyParams, '');
        
        // Não permitir alteração de company_id
        $model->company_id = $this->getCompanyId();

        if ($model->save()) {
            return $this->successResponse($model, 'Veículo atualizado com sucesso');
        }

        return $this->errorResponse('Erro ao atualizar veículo', 400, $model->errors);
    }

    /**
     * Obter histórico de manutenções do veículo
     * 
     * @param integer $id Vehicle ID
     * @return ActiveDataProvider
     */
    public function actionMaintenances($id)
    {
        $vehicle = $this->findModel($id);

        $query = $vehicle->getMaintenances()
            ->orderBy(['data_manutencao' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);
    }

    /**
     * Obter histórico de abastecimentos do veículo
     * 
     * @param integer $id Vehicle ID
     * @return ActiveDataProvider
     */
    public function actionFuelLogs($id)
    {
        $vehicle = $this->findModel($id);

        $query = $vehicle->getFuelLogs()
            ->orderBy(['data_abastecimento' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);
    }

    /**
     * Obter estatísticas do veículo
     * 
     * @param integer $id Vehicle ID
     * @return array
     */
    public function actionStats($id)
    {
        $vehicle = $this->findModel($id);

        $stats = [
            'vehicle_info' => [
                'id' => $vehicle->id,
                'license_plate' => $vehicle->license_plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'status' => $vehicle->status,
                'current_mileage' => $vehicle->mileage,
            ],
            'maintenance_stats' => [
                'total_maintenances' => $vehicle->getMaintenances()->count(),
                'pending_maintenances' => $vehicle->getMaintenances()
                    ->where(['status' => 'scheduled'])
                    ->count(),
                'completed_maintenances' => $vehicle->getMaintenances()
                    ->where(['status' => 'completed'])
                    ->count(),
                'total_maintenance_cost' => $vehicle->getMaintenances()
                    ->sum('cost') ?? 0,
            ],
            'fuel_stats' => [
                'total_fuel_logs' => $vehicle->getFuelLogs()->count(),
                'total_liters' => $vehicle->getFuelLogs()->sum('litros') ?? 0,
                'total_fuel_cost' => $vehicle->getFuelLogs()->sum('custo_total') ?? 0,
                'average_consumption' => $this->calculateAverageConsumption($vehicle),
            ],
        ];

        return $this->successResponse($stats);
    }

    /**
     * Listar veículos por status
     * 
     * @param string $status
     * @return ActiveDataProvider
     */
    public function actionByStatus($status)
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Empresa não identificada no token');
        }

        $validStatuses = ['active', 'maintenance', 'inactive'];
        if (!in_array($status, $validStatuses)) {
            throw new \yii\web\BadRequestHttpException('Status inválido');
        }

        $query = Vehicle::find()
            ->where(['company_id' => $companyId, 'status' => $status])
            ->with(['company']);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = Vehicle::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->with(['company'])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Veículo não encontrado');
        }

        return $model;
    }

    /**
     * Calcular consumo médio do veículo
     * 
     * @param Vehicle $vehicle
     * @return float
     */
    private function calculateAverageConsumption($vehicle)
    {
        $fuelLogs = $vehicle->getFuelLogs()
            ->orderBy(['quilometragem' => SORT_ASC])
            ->all();

        if (count($fuelLogs) < 2) {
            return 0;
        }

        $totalDistance = 0;
        $totalLiters = 0;

        for ($i = 1; $i < count($fuelLogs); $i++) {
            $distance = $fuelLogs[$i]->quilometragem - $fuelLogs[$i-1]->quilometragem;
            if ($distance > 0) {
                $totalDistance += $distance;
                $totalLiters += $fuelLogs[$i]->litros;
            }
        }

        return $totalDistance > 0 ? round(($totalLiters / $totalDistance) * 100, 2) : 0;
    }
}
