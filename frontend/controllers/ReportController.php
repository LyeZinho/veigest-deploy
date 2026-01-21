<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Vehicle;
use common\models\Document;
use common\models\FuelLog;
use common\models\Maintenance;
use common\models\Alert;
use common\models\User;

/**
 * ReportController - Operational Reports
 * 
 * Access Control:
 * - Admin: NO ACCESS (frontend blocked)
 * - Manager: FULL ACCESS (view, create, export)
 * - Driver: NO ACCESS (reports not visible to drivers)
 * 
 * Provides reports for:
 * - Operational overview
 * - Fuel consumption
 * - Maintenance costs
 * - Document status
 * - Vehicle analysis
 */
class ReportController extends Controller
{
    /**
     * @var string Dashboard layout
     */
    public $layout = 'dashboard';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Block admin from frontend
                    [
                        'allow' => false,
                        'roles' => ['admin'],
                        'denyCallback' => function ($rule, $action) {
                            throw new ForbiddenHttpException(
                                'Administrators do not have access to the frontend.'
                            );
                        },
                    ],
                    // View reports - manager only
                    [
                        'allow' => true,
                        'actions' => ['index', 'fuel', 'maintenance', 'vehicles', 'documents'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('reports.view');
                        },
                    ],
                    // Export reports - manager only
                    [
                        'allow' => true,
                        'actions' => ['export-csv', 'export-pdf'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('reports.export');
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'export-csv' => ['GET'],
                    'export-pdf' => ['GET'],
                ],
            ],
        ];
    }

    /**
     * Página principal de relatórios com visão geral.
     * 
     * @return string
     */
    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        // Parâmetros de filtro
        $period = Yii::$app->request->get('period', 'month');
        $vehicleId = Yii::$app->request->get('vehicle_id');
        
        // Calcular datas com base no período
        $dates = $this->calculateDateRange($period);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        
        // Estatísticas gerais
        $stats = $this->getGeneralStats($companyId, $startDate, $endDate, $vehicleId);
        
        // Dados para gráficos
        $fuelMonthly = FuelLog::getMonthlyConsumption($companyId, 6);
        $maintenanceMonthly = Maintenance::getMonthlyCosts($companyId, 6);
        $maintenanceByCate = Maintenance::getCostsByType($companyId, $startDate, $endDate);
        $documentStats = Document::getStatsByCompany($companyId);
        
        // Lista de veículos para filtro
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'stats' => $stats,
            'fuelMonthly' => $fuelMonthly,
            'maintenanceMonthly' => $maintenanceMonthly,
            'maintenanceByCategory' => $maintenanceByCate,
            'documentStats' => $documentStats,
            'vehicles' => $vehicles,
            'period' => $period,
            'vehicleId' => $vehicleId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Relatório detalhado de combustível.
     * 
     * @return string
     */
    public function actionFuel()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        // Parâmetros de filtro
        $period = Yii::$app->request->get('period', 'month');
        $vehicleId = Yii::$app->request->get('vehicle_id');
        
        $dates = $this->calculateDateRange($period);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        
        // Estatísticas de combustível
        $fuelStats = FuelLog::getStatsByCompany($companyId, $startDate, $endDate);
        $fuelMonthly = FuelLog::getMonthlyConsumption($companyId, 12);
        $fuelByVehicle = FuelLog::getConsumptionByVehicle($companyId, $startDate, $endDate);
        
        // Últimos abastecimentos
        $query = FuelLog::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['date' => SORT_DESC])
            ->limit(20);
        
        if ($vehicleId) {
            $query->andWhere(['vehicle_id' => $vehicleId]);
        }
        
        $recentFuelLogs = $query->all();
        
        // Lista de veículos para filtro
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();

        return $this->render('fuel', [
            'fuelStats' => $fuelStats,
            'fuelMonthly' => $fuelMonthly,
            'fuelByVehicle' => $fuelByVehicle,
            'recentFuelLogs' => $recentFuelLogs,
            'vehicles' => $vehicles,
            'period' => $period,
            'vehicleId' => $vehicleId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Relatório detalhado de manutenções.
     * 
     * @return string
     */
    public function actionMaintenance()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        // Parâmetros de filtro
        $period = Yii::$app->request->get('period', 'month');
        $vehicleId = Yii::$app->request->get('vehicle_id');
        
        $dates = $this->calculateDateRange($period);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        
        // Estatísticas de manutenção
        $maintenanceStats = Maintenance::getStatsByCompany($companyId, $startDate, $endDate);
        $maintenanceMonthly = Maintenance::getMonthlyCosts($companyId, 12);
        $maintenanceByType = Maintenance::getCostsByType($companyId, $startDate, $endDate);
        $maintenanceByVehicle = Maintenance::getCostsByVehicle($companyId, $startDate, $endDate);
        $upcomingMaintenance = Maintenance::getUpcoming($companyId, 30);
        
        // Últimas manutenções
        $query = Maintenance::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['date' => SORT_DESC])
            ->limit(20);
        
        if ($vehicleId) {
            $query->andWhere(['vehicle_id' => $vehicleId]);
        }
        
        $recentMaintenance = $query->all();
        
        // Lista de veículos para filtro
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();

        return $this->render('maintenance', [
            'maintenanceStats' => $maintenanceStats,
            'maintenanceMonthly' => $maintenanceMonthly,
            'maintenanceByType' => $maintenanceByType,
            'maintenanceByVehicle' => $maintenanceByVehicle,
            'upcomingMaintenance' => $upcomingMaintenance,
            'recentMaintenance' => $recentMaintenance,
            'vehicles' => $vehicles,
            'period' => $period,
            'vehicleId' => $vehicleId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Relatório de análise por veículo.
     * 
     * @return string
     */
    public function actionVehicles()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        // Parâmetros de filtro
        $period = Yii::$app->request->get('period', 'year');
        
        $dates = $this->calculateDateRange($period);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        
        // Dados por veículo
        $vehiclesData = $this->getVehiclesAnalysis($companyId, $startDate, $endDate);
        
        // Estatísticas gerais de veículos
        $vehicleStats = [
            'total' => Vehicle::find()->where(['company_id' => $companyId])->count(),
            'active' => Vehicle::find()->where(['company_id' => $companyId, 'status' => Vehicle::STATUS_ACTIVE])->count(),
            'maintenance' => Vehicle::find()->where(['company_id' => $companyId, 'status' => Vehicle::STATUS_MAINTENANCE])->count(),
            'inactive' => Vehicle::find()->where(['company_id' => $companyId, 'status' => Vehicle::STATUS_INACTIVE])->count(),
        ];

        return $this->render('vehicles', [
            'vehiclesAnalysis' => $vehiclesData,
            'vehicleStats' => $vehicleStats,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Retorna dados de relatório em formato JSON para AJAX.
     * 
     * @return array
     */
    public function actionGetChartData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $companyId = Yii::$app->user->identity->company_id;
        $type = Yii::$app->request->get('type', 'fuel_monthly');
        $months = (int) Yii::$app->request->get('months', 6);
        
        switch ($type) {
            case 'fuel_monthly':
                return FuelLog::getMonthlyConsumption($companyId, $months);
            case 'maintenance_monthly':
                return Maintenance::getMonthlyCosts($companyId, $months);
            case 'maintenance_by_type':
                return Maintenance::getCostsByType($companyId);
            case 'fuel_by_vehicle':
                return FuelLog::getConsumptionByVehicle($companyId);
            default:
                return ['error' => 'Tipo de gráfico não reconhecido'];
        }
    }

    /**
     * Exporta relatório para CSV.
     * 
     * @param string $type Tipo de relatório
     * @return Response
     */
    public function actionExportCsv($type = 'general')
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $period = Yii::$app->request->get('period', 'month');
        $dates = $this->calculateDateRange($period);
        
        $filename = "relatorio_{$type}_" . date('Y-m-d') . ".csv";
        $content = $this->generateCsvContent($type, $companyId, $dates['start'], $dates['end']);
        
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'text/csv; charset=utf-8');
        Yii::$app->response->headers->add('Content-Disposition', "attachment; filename=\"{$filename}\"");
        
        // BOM para UTF-8
        return "\xEF\xBB\xBF" . $content;
    }

    /**
     * Calcula o intervalo de datas com base no período selecionado.
     * 
     * @param string $period
     * @return array
     */
    protected function calculateDateRange($period)
    {
        $endDate = date('Y-m-d');
        
        switch ($period) {
            case 'week':
                $startDate = date('Y-m-d', strtotime('-7 days'));
                break;
            case 'month':
                $startDate = date('Y-m-01');
                break;
            case '3months':
                $startDate = date('Y-m-d', strtotime('-3 months'));
                break;
            case '6months':
                $startDate = date('Y-m-d', strtotime('-6 months'));
                break;
            case 'year':
                $startDate = date('Y-01-01');
                break;
            case 'all':
                $startDate = '2000-01-01';
                break;
            default:
                $startDate = date('Y-m-01');
        }
        
        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    /**
     * Obtém estatísticas gerais para o dashboard.
     * 
     * @param int $companyId
     * @param string $startDate
     * @param string $endDate
     * @param int|null $vehicleId
     * @return array
     */
    protected function getGeneralStats($companyId, $startDate, $endDate, $vehicleId = null)
    {
        // Combustível
        $fuelStats = FuelLog::getStatsByCompany($companyId, $startDate, $endDate);
        
        // Manutenção
        $maintenanceStats = Maintenance::getStatsByCompany($companyId, $startDate, $endDate);
        
        // Veículos
        $totalVehicles = Vehicle::find()->where(['company_id' => $companyId])->count();
        $activeVehicles = Vehicle::find()->where(['company_id' => $companyId, 'status' => Vehicle::STATUS_ACTIVE])->count();
        
        // Quilometragem total (soma da quilometragem de todos os veículos)
        $totalMileage = Vehicle::find()->where(['company_id' => $companyId])->sum('mileage') ?: 0;
        
        // Custos totais
        $totalCosts = $fuelStats['total_value'] + $maintenanceStats['total_cost'];
        
        // Alertas ativos
        $activeAlerts = Alert::find()
            ->where(['company_id' => $companyId, 'status' => Alert::STATUS_ACTIVE])
            ->count();
        
        return [
            'total_vehicles' => (int) $totalVehicles,
            'active_vehicles' => (int) $activeVehicles,
            'total_mileage' => (int) $totalMileage,
            'fuel_liters' => $fuelStats['total_liters'],
            'fuel_cost' => $fuelStats['total_value'],
            'fuel_avg_price' => $fuelStats['avg_price_per_liter'],
            'maintenance_cost' => $maintenanceStats['total_cost'],
            'maintenance_count' => $maintenanceStats['total_records'],
            'upcoming_maintenance' => $maintenanceStats['upcoming'],
            'total_costs' => round($totalCosts, 2),
            'active_alerts' => (int) $activeAlerts,
        ];
    }

    /**
     * Análise detalhada por veículo.
     * 
     * @param int $companyId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    protected function getVehiclesAnalysis($companyId, $startDate, $endDate)
    {
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();
        
        $result = [];
        
        foreach ($vehicles as $vehicle) {
            // Combustível
            $fuelQuery = FuelLog::find()
                ->where(['vehicle_id' => $vehicle->id])
                ->andWhere(['>=', 'date', $startDate])
                ->andWhere(['<=', 'date', $endDate]);
            
            $fuelLiters = (float) $fuelQuery->sum('liters') ?: 0;
            $fuelCost = (float) $fuelQuery->sum('value') ?: 0;
            
            // Manutenção
            $maintenanceQuery = Maintenance::find()
                ->where(['vehicle_id' => $vehicle->id])
                ->andWhere(['>=', 'date', $startDate])
                ->andWhere(['<=', 'date', $endDate]);
            
            $maintenanceCost = (float) $maintenanceQuery->sum('cost') ?: 0;
            $maintenanceCount = (int) $maintenanceQuery->count();
            
            // Documentos
            $documentsExpiring = Document::find()
                ->where(['vehicle_id' => $vehicle->id])
                ->andWhere(['status' => 'valid'])
                ->andWhere(['<=', 'expiry_date', date('Y-m-d', strtotime('+30 days'))])
                ->andWhere(['>', 'expiry_date', date('Y-m-d')])
                ->count();
            
            $documentsExpired = Document::find()
                ->where(['vehicle_id' => $vehicle->id])
                ->andWhere(['status' => 'expired'])
                ->count();
            
            $result[] = [
                'license_plate' => $vehicle->license_plate,
                'brand' => $vehicle->brand,
                'model' => $vehicle->model,
                'fuel_cost' => round($fuelCost, 2),
                'fuel_count' => (int) $fuelQuery->count(),
                'maintenance_cost' => round($maintenanceCost, 2),
                'maintenance_count' => $maintenanceCount,
                'total_cost' => round($fuelCost + $maintenanceCost, 2),
                'document_count' => (int) ($documentsExpiring + $documentsExpired),
            ];
        }
        
        return $result;
    }

    /**
     * Gera conteúdo CSV para exportação.
     * 
     * @param string $type
     * @param int $companyId
     * @param string $startDate
     * @param string $endDate
     * @return string
     */
    protected function generateCsvContent($type, $companyId, $startDate, $endDate)
    {
        $output = "";
        
        switch ($type) {
            case 'fuel':
                $output = "Data;Veículo;Litros;Valor;Preço/Litro;Quilometragem;Observações\n";
                $records = FuelLog::find()
                    ->where(['company_id' => $companyId])
                    ->andWhere(['>=', 'date', $startDate])
                    ->andWhere(['<=', 'date', $endDate])
                    ->orderBy(['date' => SORT_DESC])
                    ->all();
                
                foreach ($records as $record) {
                    $vehicle = $record->vehicle ? $record->vehicle->license_plate : '-';
                    $output .= implode(';', [
                        $record->date,
                        $vehicle,
                        number_format($record->liters, 2, ',', ''),
                        number_format($record->value, 2, ',', ''),
                        number_format($record->price_per_liter, 4, ',', ''),
                        $record->current_mileage ?: '-',
                        $record->notes ?: '-',
                    ]) . "\n";
                }
                break;
                
            case 'maintenance':
                $output = "Data;Veículo;Tipo;Descrição;Custo;Oficina;Quilometragem\n";
                $records = Maintenance::find()
                    ->where(['company_id' => $companyId])
                    ->andWhere(['>=', 'date', $startDate])
                    ->andWhere(['<=', 'date', $endDate])
                    ->orderBy(['date' => SORT_DESC])
                    ->all();
                
                foreach ($records as $record) {
                    $vehicle = $record->vehicle ? $record->vehicle->license_plate : '-';
                    $output .= implode(';', [
                        $record->date,
                        $vehicle,
                        $record->getTypeLabel(),
                        str_replace(';', ',', $record->description ?: '-'),
                        number_format($record->cost, 2, ',', ''),
                        $record->workshop ?: '-',
                        $record->mileage_record ?: '-',
                    ]) . "\n";
                }
                break;
                
            case 'vehicles':
                $output = "Matrícula;Marca;Modelo;Ano;Estado;Quilometragem;Combustível Total (€);Manutenção Total (€);Custo Total (€)\n";
                $vehiclesData = $this->getVehiclesAnalysis($companyId, $startDate, $endDate);
                
                foreach ($vehiclesData as $data) {
                    $vehicle = $data['vehicle'];
                    $output .= implode(';', [
                        $vehicle->license_plate,
                        $vehicle->brand,
                        $vehicle->model,
                        $vehicle->year ?: '-',
                        $vehicle->getStatusList()[$vehicle->status] ?? $vehicle->status,
                        $vehicle->mileage,
                        number_format($data['fuel_cost'], 2, ',', ''),
                        number_format($data['maintenance_cost'], 2, ',', ''),
                        number_format($data['total_cost'], 2, ',', ''),
                    ]) . "\n";
                }
                break;
                
            default:
                $stats = $this->getGeneralStats($companyId, $startDate, $endDate);
                $output = "Relatório Geral - Período: {$startDate} a {$endDate}\n\n";
                $output .= "Métrica;Valor\n";
                $output .= "Total de Veículos;" . $stats['total_vehicles'] . "\n";
                $output .= "Veículos Ativos;" . $stats['active_vehicles'] . "\n";
                $output .= "Quilometragem Total;" . number_format($stats['total_mileage'], 0, ',', '.') . "\n";
                $output .= "Combustível (Litros);" . number_format($stats['fuel_liters'], 2, ',', '') . "\n";
                $output .= "Combustível (€);" . number_format($stats['fuel_cost'], 2, ',', '') . "\n";
                $output .= "Manutenção (€);" . number_format($stats['maintenance_cost'], 2, ',', '') . "\n";
                $output .= "Custos Totais (€);" . number_format($stats['total_costs'], 2, ',', '') . "\n";
        }
        
        return $output;
    }

    /**
     * Retorna opções de períodos para filtros
     * 
     * @return array
     */
    public static function getPeriodOptions()
    {
        return [
            'week' => 'Última Semana',
            'month' => 'Este Mês',
            '3months' => 'Últimos 3 Meses',
            '6months' => 'Últimos 6 Meses',
            'year' => 'Este Ano',
            'all' => 'Todo o Período',
        ];
    }
}
