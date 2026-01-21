<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\FuelLog;

/**
 * FuelLog API Controller
 * 
 * Fornece operações CRUD para registros de abastecimento com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * .
 */
class FuelLogController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\FuelLog';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        // Customizar as ações padrão
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

    /**
     * Lista todos os registros de abastecimento da empresa do usuário autenticado
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $query = FuelLog::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId]);

        // Filtros opcionais
        $request = Yii::$app->request;
        
        if ($vehicleId = $request->get('vehicle_id')) {
            $query->andWhere(['{{%fuel_logs}}.vehicle_id' => $vehicleId]);
        }
        
        if ($startDate = $request->get('start_date')) {
            $query->andWhere(['>=', 'date', $startDate]);
        }
        
        if ($endDate = $request->get('end_date')) {
            $query->andWhere(['<=', 'date', $endDate]);
        }

        if ($search = $request->get('search')) {
            $query->andWhere([
                'or',
                ['like', 'notes', $search]
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $request->get('per-page', 20),
                'page' => $request->get('page', 1) - 1,
            ],
            'sort' => [
                'defaultOrder' => ['date' => SORT_DESC],
                'attributes' => [
                    'id', 
                    'date', 
                    'liters', 
                    'value', 
                    'current_mileage', 
                    'price_per_liter',
                    'created_at'
                ]
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Cria novo registro de abastecimento
     * Automaticamente associa à empresa do usuário autenticado através do veículo
     * 
     * @return FuelLog
     */
    public function actionCreate()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $model = new FuelLog();
        $model->load(Yii::$app->request->bodyParams, '');

        // Verificar se o veículo pertence à empresa do usuário
        if ($model->vehicle_id) {
            $vehicle = \backend\modules\api\models\Vehicle::findOne($model->vehicle_id);
            if (!$vehicle || $vehicle->company_id != $companyId) {
                return $this->errorResponse('Veículo não encontrado ou não pertence à sua empresa', 403);
            }
        }

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        return $this->errorResponse('Erro ao criar registro de abastecimento', 422, $model->errors);
    }

    /**
     * Atualiza um registro de abastecimento
     * 
     * @param int $id
     * @return FuelLog
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $model->load(Yii::$app->request->bodyParams, '');

        // Se mudando de veículo, verificar se o novo veículo pertence à empresa
        $companyId = $this->getCompanyId();
        if ($model->vehicle_id) {
            $vehicle = \backend\modules\api\models\Vehicle::findOne($model->vehicle_id);
            if (!$vehicle || $vehicle->company_id != $companyId) {
                return $this->errorResponse('Veículo não encontrado ou não pertence à sua empresa', 403);
            }
        }

        if ($model->save()) {
            return $model;
        }

        return $this->errorResponse('Erro ao atualizar registro de abastecimento', 422, $model->errors);
    }

    /**
     * Lista registros de abastecimento por veículo
     * 
     * @param int $vehicle_id
     * @return ActiveDataProvider
     */
    public function actionByVehicle($vehicle_id)
    {
        $companyId = $this->getCompanyId();
        
        // Verificar se o veículo pertence à empresa
        $vehicle = \backend\modules\api\models\Vehicle::findOne($vehicle_id);
        if (!$vehicle || $vehicle->company_id != $companyId) {
            throw new NotFoundHttpException('Veículo não encontrado');
        }

        $query = FuelLog::find()->where(['vehicle_id' => $vehicle_id]);
        
        // Filtros opcionais
        $request = Yii::$app->request;
        if ($startDate = $request->get('start_date')) {
            $query->andWhere(['>=', 'date', $startDate]);
        }
        if ($endDate = $request->get('end_date')) {
            $query->andWhere(['<=', 'date', $endDate]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $request->get('per-page', 20)],
            'sort' => ['defaultOrder' => ['date' => SORT_DESC]],
        ]);
    }

    /**
     * Estatísticas de consumo
     * 
     * @return array
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $request = Yii::$app->request;
        $vehicleId = $request->get('vehicle_id');
        $period = $request->get('period', 'monthly'); // monthly, weekly, yearly

        $query = FuelLog::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId]);

        if ($vehicleId) {
            $query->andWhere(['{{%fuel_logs}}.vehicle_id' => $vehicleId]);
        }

        // Filtro de período
        $startDate = $this->getStartDateByPeriod($period);
        if ($startDate) {
            $query->andWhere(['>=', 'date', $startDate]);
        }

        $fuelLogs = $query->orderBy(['date' => SORT_ASC])->all();

        return [
            'period' => $period,
            'vehicle_id' => $vehicleId,
            'summary' => [
                'total_fuel_logs' => count($fuelLogs),
                'total_liters' => array_sum(array_map(function($f) { return $f->liters; }, $fuelLogs)),
                'total_cost' => array_sum(array_map(function($f) { return $f->value; }, $fuelLogs)),
                'average_price_per_liter' => $this->calculateAveragePricePerLiter($fuelLogs),
                'fuel_efficiency' => $this->calculateFuelEfficiency($fuelLogs),
                'cost_per_km' => $this->calculateCostPerKm($fuelLogs),
            ],
            'by_vehicle' => $vehicleId ? null : $this->groupFuelLogsByVehicle($fuelLogs),
            'monthly_trend' => $this->calculateMonthlyTrend($fuelLogs),
        ];
    }

    /**
     * Alertas de baixo combustível (baseado em padrões de consumo)
     * 
     * @return array
     */
    public function actionAlerts()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $alerts = [];
        
        // Buscar veículos da empresa
        $vehicles = \backend\modules\api\models\Vehicle::find()
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->all();

        foreach ($vehicles as $vehicle) {
            // Último abastecimento
            $lastFuelLog = FuelLog::find()
                ->where(['vehicle_id' => $vehicle->id])
                ->orderBy(['date' => SORT_DESC])
                ->one();

            if ($lastFuelLog) {
                $daysSinceLastFuel = floor((time() - strtotime($lastFuelLog->date)) / (24 * 60 * 60));
                $kmSinceLastFuel = $vehicle->mileage - ($lastFuelLog->current_mileage ?? 0);

                // Critérios para alerta (customizáveis)
                $maxDaysWithoutFuel = 30;
                $maxKmWithoutFuel = 1000;

                if ($daysSinceLastFuel > $maxDaysWithoutFuel || $kmSinceLastFuel > $maxKmWithoutFuel) {
                    $alerts[] = [
                        'type' => 'fuel_alert',
                        'vehicle' => [
                            'id' => $vehicle->id,
                            'license_plate' => $vehicle->license_plate,
                            'brand' => $vehicle->brand,
                            'model' => $vehicle->model,
                        ],
                        'last_fuel_date' => $lastFuelLog->date,
                        'days_since_last_fuel' => $daysSinceLastFuel,
                        'km_since_last_fuel' => $kmSinceLastFuel,
                        'message' => "Veículo {$vehicle->license_plate} pode estar com baixo combustível",
                        'priority' => $daysSinceLastFuel > 45 || $kmSinceLastFuel > 1500 ? 'high' : 'medium',
                    ];
                }
            } else {
                // Veículo sem nenhum registro de abastecimento
                $alerts[] = [
                    'type' => 'no_fuel_records',
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'license_plate' => $vehicle->license_plate,
                        'brand' => $vehicle->brand,
                        'model' => $vehicle->model,
                    ],
                    'message' => "Veículo {$vehicle->license_plate} não possui registros de abastecimento",
                    'priority' => 'low',
                ];
            }
        }

        return [
            'total_alerts' => count($alerts),
            'alerts' => $alerts,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Relatório de eficiência de combustível
     * 
     * @return array
     */
    public function actionEfficiencyReport()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $request = Yii::$app->request;
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));

        $query = FuelLog::find()
            ->joinWith('vehicle')
            ->where(['{{%vehicles}}.company_id' => $companyId])
            ->andWhere(['between', 'date', $startDate, $endDate]);

        $fuelLogs = $query->orderBy(['vehicle_id' => SORT_ASC, 'date' => SORT_ASC])->all();

        $vehicleEfficiency = $this->calculateVehicleEfficiency($fuelLogs);

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_vehicles' => count($vehicleEfficiency),
                'total_fuel_cost' => array_sum(array_map(function($f) { return $f->value; }, $fuelLogs)),
                'total_liters' => array_sum(array_map(function($f) { return $f->liters; }, $fuelLogs)),
                'fleet_average_efficiency' => $this->calculateFleetAverageEfficiency($vehicleEfficiency),
            ],
            'vehicle_efficiency' => $vehicleEfficiency,
            'recommendations' => $this->generateEfficiencyRecommendations($vehicleEfficiency),
        ];
    }

    /**
     * Busca modelo por ID e verifica acesso
     * 
     * @param int $id
     * @return FuelLog
     * @throws NotFoundHttpException|ForbiddenHttpException
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Company ID não encontrado no token de autenticação');
        }

        $model = FuelLog::find()
            ->joinWith('vehicle')
            ->where(['{{%fuel_logs}}.id' => $id])
            ->andWhere(['{{%vehicles}}.company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Registro de abastecimento não encontrado');
        }

        return $model;
    }

    /**
     * Obtém data de início baseada no período
     * 
     * @param string $period
     * @return string|null
     */
    private function getStartDateByPeriod($period)
    {
        switch ($period) {
            case 'weekly':
                return date('Y-m-d', strtotime('-1 week'));
            case 'monthly':
                return date('Y-m-01');
            case 'yearly':
                return date('Y-01-01');
            default:
                return null;
        }
    }

    /**
     * Calcula preço médio por litro
     * 
     * @param array $fuelLogs
     * @return float
     */
    private function calculateAveragePricePerLiter($fuelLogs)
    {
        if (empty($fuelLogs)) return 0;

        $totalCost = array_sum(array_map(function($f) { return $f->value; }, $fuelLogs));
        $totalLiters = array_sum(array_map(function($f) { return $f->liters; }, $fuelLogs));

        return $totalLiters > 0 ? round($totalCost / $totalLiters, 3) : 0;
    }

    /**
     * Calcula eficiência de combustível média
     * 
     * @param array $fuelLogs
     * @return float
     */
    private function calculateFuelEfficiency($fuelLogs)
    {
        if (count($fuelLogs) < 2) return 0;

        $totalDistance = 0;
        $totalFuel = 0;

        for ($i = 1; $i < count($fuelLogs); $i++) {
            $current = $fuelLogs[$i];
            $previous = $fuelLogs[$i - 1];

            if ($current->current_mileage && $previous->current_mileage) {
                $distance = $current->current_mileage - $previous->current_mileage;
                if ($distance > 0) {
                    $totalDistance += $distance;
                    $totalFuel += $current->liters;
                }
            }
        }

        return $totalFuel > 0 ? round($totalDistance / $totalFuel, 2) : 0;
    }

    /**
     * Calcula custo por quilômetro
     * 
     * @param array $fuelLogs
     * @return float
     */
    private function calculateCostPerKm($fuelLogs)
    {
        if (count($fuelLogs) < 2) return 0;

        $totalDistance = 0;
        $totalCost = array_sum(array_map(function($f) { return $f->value; }, $fuelLogs));

        for ($i = 1; $i < count($fuelLogs); $i++) {
            $current = $fuelLogs[$i];
            $previous = $fuelLogs[$i - 1];

            if ($current->current_mileage && $previous->current_mileage) {
                $distance = $current->current_mileage - $previous->current_mileage;
                if ($distance > 0) {
                    $totalDistance += $distance;
                }
            }
        }

        return $totalDistance > 0 ? round($totalCost / $totalDistance, 3) : 0;
    }

    /**
     * Agrupa registros por veículo
     * 
     * @param array $fuelLogs
     * @return array
     */
    private function groupFuelLogsByVehicle($fuelLogs)
    {
        $grouped = [];

        foreach ($fuelLogs as $fuelLog) {
            $vehicleId = $fuelLog->vehicle_id;
            
            if (!isset($grouped[$vehicleId])) {
                $grouped[$vehicleId] = [
                    'vehicle' => $fuelLog->vehicle ? [
                        'id' => $fuelLog->vehicle->id,
                        'license_plate' => $fuelLog->vehicle->license_plate,
                        'brand' => $fuelLog->vehicle->brand,
                        'model' => $fuelLog->vehicle->model,
                    ] : null,
                    'total_liters' => 0,
                    'total_cost' => 0,
                    'fuel_logs_count' => 0,
                    'fuel_logs' => [],
                ];
            }

            $grouped[$vehicleId]['total_liters'] += $fuelLog->liters;
            $grouped[$vehicleId]['total_cost'] += $fuelLog->value;
            $grouped[$vehicleId]['fuel_logs_count']++;
            $grouped[$vehicleId]['fuel_logs'][] = $fuelLog;
        }

        return array_values($grouped);
    }

    /**
     * Calcula tendência mensal
     * 
     * @param array $fuelLogs
     * @return array
     */
    private function calculateMonthlyTrend($fuelLogs)
    {
        $monthly = [];

        foreach ($fuelLogs as $fuelLog) {
            $month = date('Y-m', strtotime($fuelLog->date));
            
            if (!isset($monthly[$month])) {
                $monthly[$month] = [
                    'month' => $month,
                    'total_liters' => 0,
                    'total_cost' => 0,
                    'fuel_logs_count' => 0,
                ];
            }

            $monthly[$month]['total_liters'] += $fuelLog->liters;
            $monthly[$month]['total_cost'] += $fuelLog->value;
            $monthly[$month]['fuel_logs_count']++;
        }

        return array_values($monthly);
    }

    /**
     * Calcula eficiência por veículo
     * 
     * @param array $fuelLogs
     * @return array
     */
    private function calculateVehicleEfficiency($fuelLogs)
    {
        $byVehicle = $this->groupFuelLogsByVehicle($fuelLogs);
        $efficiency = [];

        foreach ($byVehicle as $vehicleData) {
            $vehicleFuelLogs = $vehicleData['fuel_logs'];
            
            if (count($vehicleFuelLogs) >= 2) {
                // Ordenar por data
                usort($vehicleFuelLogs, function($a, $b) {
                    return strtotime($a->date) - strtotime($b->date);
                });

                $efficiency[] = [
                    'vehicle' => $vehicleData['vehicle'],
                    'total_cost' => $vehicleData['total_cost'],
                    'total_liters' => $vehicleData['total_liters'],
                    'fuel_efficiency' => $this->calculateFuelEfficiency($vehicleFuelLogs),
                    'cost_per_km' => $this->calculateCostPerKm($vehicleFuelLogs),
                    'average_price_per_liter' => $this->calculateAveragePricePerLiter($vehicleFuelLogs),
                ];
            }
        }

        return $efficiency;
    }

    /**
     * Calcula eficiência média da frota
     * 
     * @param array $vehicleEfficiency
     * @return float
     */
    private function calculateFleetAverageEfficiency($vehicleEfficiency)
    {
        if (empty($vehicleEfficiency)) return 0;

        $totalEfficiency = array_sum(array_map(function($v) { return $v['fuel_efficiency']; }, $vehicleEfficiency));
        return round($totalEfficiency / count($vehicleEfficiency), 2);
    }

    /**
     * Gera recomendações baseadas na eficiência
     * 
     * @param array $vehicleEfficiency
     * @return array
     */
    private function generateEfficiencyRecommendations($vehicleEfficiency)
    {
        $recommendations = [];

        if (empty($vehicleEfficiency)) {
            return ['Nenhum dado suficiente para recomendações'];
        }

        // Encontrar o veículo menos eficiente
        $leastEfficient = min($vehicleEfficiency);
        $mostEfficient = max($vehicleEfficiency);

        if ($leastEfficient['fuel_efficiency'] < 8) {
            $recommendations[] = "Veículo {$leastEfficient['vehicle']['license_plate']} tem baixa eficiência ({$leastEfficient['fuel_efficiency']} km/l). Considere manutenção.";
        }

        if ($mostEfficient['fuel_efficiency'] - $leastEfficient['fuel_efficiency'] > 5) {
            $recommendations[] = "Grande variação na eficiência da frota. Revisar padrões de condução e manutenção.";
        }

        $avgCostPerKm = array_sum(array_map(function($v) { return $v['cost_per_km']; }, $vehicleEfficiency)) / count($vehicleEfficiency);
        if ($avgCostPerKm > 0.5) {
            $recommendations[] = "Custo por km da frota está elevado (R$ {$avgCostPerKm}/km). Considerar otimizações.";
        }

        return empty($recommendations) ? ['Frota com boa eficiência geral'] : $recommendations;
    }
}
