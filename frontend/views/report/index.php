<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\controllers\ReportController;

/** @var yii\web\View $this */
/** @var array $stats */
/** @var array $fuelMonthly */
/** @var array $maintenanceMonthly */
/** @var array $maintenanceByCategory */
/** @var array $documentStats */
/** @var common\models\Vehicle[] $vehicles */
/** @var string $period */
/** @var int|null $vehicleId */
/** @var string $startDate */
/** @var string $endDate */

$this->title = 'Relatórios Operacionais';
$this->params['breadcrumbs'][] = $this->title;

// Preparar dados para os gráficos
$fuelLabels = array_column($fuelMonthly, 'month_label');
$fuelValues = array_column($fuelMonthly, 'total_value');
$fuelLiters = array_column($fuelMonthly, 'total_liters');

$maintenanceLabels = array_column($maintenanceMonthly, 'month_label');
$maintenanceCosts = array_column($maintenanceMonthly, 'total_cost');

$categoryLabels = [];
$categoryValues = [];
foreach ($maintenanceByCategory as $cat) {
    $types = \common\models\Maintenance::getTypesList();
    $categoryLabels[] = $types[$cat['type']] ?? $cat['type'];
    $categoryValues[] = (float) $cat['total_cost'];
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-chart-bar mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group">
                    <?= Html::a('<i class="fas fa-gas-pump mr-1"></i>Combustível', ['fuel'], [
                        'class' => 'btn btn-outline-primary'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-wrench mr-1"></i>Manutenção', ['maintenance'], [
                        'class' => 'btn btn-outline-primary'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-car mr-1"></i>Veículos', ['vehicles'], [
                        'class' => 'btn btn-outline-primary'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <?= Html::beginForm(Url::to(['report/index'], true), 'get') ?>
                <?= Html::hiddenInput('r', 'report/index') ?>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Período</label>
                            <?= Html::dropDownList('period', $period, ReportController::getPeriodOptions(), [
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <label>Veículo</label>
                            <?= Html::dropDownList('vehicle_id', $vehicleId, 
                                ArrayHelper::merge(['' => 'Todos os Veículos'], 
                                    ArrayHelper::map($vehicles, 'id', function($v) {
                                        return $v->license_plate . ' - ' . $v->brand . ' ' . $v->model;
                                    })
                                ), [
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-4 pt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter mr-2"></i>Aplicar Filtro
                            </button>
                        </div>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <!-- KPIs Principais -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="small-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="inner">
                        <h3><?= number_format($stats['total_vehicles']) ?></h3>
                        <p>Veículos na Frota</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <a href="<?= Url::to(['vehicles']) ?>" class="small-box-footer">
                        Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($stats['fuel_liters'], 0, ',', '.') ?> <small>L</small></h3>
                        <p>Combustível Consumido</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-gas-pump"></i>
                    </div>
                    <a href="<?= Url::to(['fuel']) ?>" class="small-box-footer">
                        Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>€<?= number_format($stats['maintenance_cost'], 0, ',', '.') ?></h3>
                        <p>Custos de Manutenção</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <a href="<?= Url::to(['maintenance']) ?>" class="small-box-footer">
                        Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>€<?= number_format($stats['total_costs'], 2, ',', '.') ?></h3>
                        <p>Custos Totais</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <a href="<?= Url::to(['export-csv', 'type' => 'general', 'period' => $period]) ?>" class="small-box-footer">
                        Exportar CSV <i class="fas fa-download"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Gráficos Principais -->
        <div class="row">
            <!-- Gráfico de Consumo de Combustível -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-gas-pump mr-2"></i>Consumo de Combustível (Mensal)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="fuelChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Custos de Manutenção -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-wrench mr-2"></i>Custos de Manutenção (Mensal)
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="maintenanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Custos por Categoria -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Custos por Tipo de Manutenção
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado dos Documentos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt mr-2"></i>Estado dos Documentos
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="documentsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Resumo -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>Resumo Operacional
                </h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <?= Html::a('<i class="fas fa-download mr-1"></i>CSV', 
                            ['export-csv', 'type' => 'general', 'period' => $period], 
                            ['class' => 'btn btn-sm btn-outline-secondary']
                        ) ?>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Métrica</th>
                            <th>Valor</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fas fa-car mr-2 text-primary"></i>Total de Veículos</td>
                            <td><strong><?= $stats['total_vehicles'] ?></strong></td>
                            <td><span class="badge badge-success"><?= $stats['active_vehicles'] ?> ativos</span></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-tachometer-alt mr-2 text-info"></i>Quilometragem Total</td>
                            <td><strong><?= number_format($stats['total_mileage'], 0, ',', '.') ?> km</strong></td>
                            <td>Soma de todos os veículos</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-gas-pump mr-2 text-warning"></i>Combustível Consumido</td>
                            <td><strong><?= number_format($stats['fuel_liters'], 2, ',', '.') ?> L</strong></td>
                            <td>€<?= number_format($stats['fuel_cost'], 2, ',', '.') ?> (€<?= number_format($stats['fuel_avg_price'], 2, ',', '.') ?>/L)</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-wrench mr-2 text-danger"></i>Manutenções</td>
                            <td><strong><?= $stats['maintenance_count'] ?> registos</strong></td>
                            <td>€<?= number_format($stats['maintenance_cost'], 2, ',', '.') ?> em custos</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar-check mr-2 text-success"></i>Manutenções Agendadas</td>
                            <td><strong><?= $stats['upcoming_maintenance'] ?></strong></td>
                            <td>Próximos 30 dias</td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-bell mr-2 text-warning"></i>Alertas Ativos</td>
                            <td><strong><?= $stats['active_alerts'] ?></strong></td>
                            <td><?= $stats['active_alerts'] > 0 ? '<span class="badge badge-warning">Requer atenção</span>' : '<span class="badge badge-success">Tudo OK</span>' ?></td>
                        </tr>
                        <tr class="table-active">
                            <td><i class="fas fa-euro-sign mr-2 text-success"></i><strong>Custos Totais</strong></td>
                            <td><strong style="font-size: 1.2em;">€<?= number_format($stats['total_costs'], 2, ',', '.') ?></strong></td>
                            <td>Período: <?= Yii::$app->formatter->asDate($startDate, 'dd/MM/yyyy') ?> - <?= Yii::$app->formatter->asDate($endDate, 'dd/MM/yyyy') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
// Registrar scripts para os gráficos
$fuelLabelsJs = json_encode($fuelLabels);
$fuelValuesJs = json_encode(array_map('floatval', $fuelValues));
$fuelLitersJs = json_encode(array_map('floatval', $fuelLiters));
$maintenanceLabelsJs = json_encode($maintenanceLabels);
$maintenanceCostsJs = json_encode(array_map('floatval', $maintenanceCosts));
$categoryLabelsJs = json_encode($categoryLabels);
$categoryValuesJs = json_encode($categoryValues);
$docValid = $documentStats['valid'] ?? 0;
$docExpiring = $documentStats['expiring_soon'] ?? 0;
$docExpired = $documentStats['expired'] ?? 0;

$js = <<<JS
// Opções comuns para os gráficos
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};

// Gráfico de Combustível
const fuelCtx = document.getElementById('fuelChart').getContext('2d');
new Chart(fuelCtx, {
    type: 'bar',
    data: {
        labels: {$fuelLabelsJs},
        datasets: [
            {
                label: 'Custo (€)',
                data: {$fuelValuesJs},
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                label: 'Litros',
                data: {$fuelLitersJs},
                type: 'line',
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: false,
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        ...chartOptions,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: { display: true, text: 'Custo (€)' }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: { display: true, text: 'Litros' },
                grid: { drawOnChartArea: false }
            }
        }
    }
});

// Gráfico de Manutenção
const maintenanceCtx = document.getElementById('maintenanceChart').getContext('2d');
new Chart(maintenanceCtx, {
    type: 'bar',
    data: {
        labels: {$maintenanceLabelsJs},
        datasets: [{
            label: 'Custo (€)',
            data: {$maintenanceCostsJs},
            backgroundColor: 'rgba(255, 193, 7, 0.8)',
            borderColor: 'rgba(255, 193, 7, 1)',
            borderWidth: 1
        }]
    },
    options: {
        ...chartOptions,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Custo (€)' }
            }
        }
    }
});

// Gráfico de Categorias
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: {$categoryLabelsJs},
        datasets: [{
            data: {$categoryValuesJs},
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)'
            ]
        }]
    },
    options: chartOptions
});

// Gráfico de Documentos
const documentsCtx = document.getElementById('documentsChart').getContext('2d');
new Chart(documentsCtx, {
    type: 'pie',
    data: {
        labels: ['Válidos', 'Próximos do Vencimento', 'Expirados'],
        datasets: [{
            data: [{$docValid}, {$docExpiring}, {$docExpired}],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: chartOptions
});
JS;

$this->registerJs($js);
?>
