<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use frontend\controllers\ReportController;

/** @var yii\web\View $this */
/** @var array $fuelStats */
/** @var array $fuelMonthly */
/** @var array $fuelByVehicle */
/** @var common\models\FuelLog[] $recentFuelLogs */
/** @var common\models\Vehicle[] $vehicles */
/** @var string $period */
/** @var int|null $vehicleId */

$this->title = 'Relatório de Combustível';
$this->params['breadcrumbs'][] = ['label' => 'Relatórios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Preparar dados para gráficos
$monthLabels = array_column($fuelMonthly, 'month_label');
$monthValues = array_column($fuelMonthly, 'total_value');
$monthLiters = array_column($fuelMonthly, 'total_liters');

$vehicleLabels = array_map(function($v) {
    return $v['license_plate'];
}, $fuelByVehicle);
$vehicleCosts = array_map(function($v) {
    return (float) $v['total_value'];
}, $fuelByVehicle);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-gas-pump mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Voltar', ['index'], [
                    'class' => 'btn btn-secondary'
                ]) ?>
                <?= Html::a('<i class="fas fa-download mr-1"></i>Exportar CSV', 
                    ['export-csv', 'type' => 'fuel', 'period' => $period], 
                    ['class' => 'btn btn-success']
                ) ?>
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
                <?= Html::beginForm(Url::to(['report/fuel'], true), 'get') ?>
                <?= Html::hiddenInput('r', 'report/fuel') ?>
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

        <!-- KPIs -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= number_format($fuelStats['total_liters'], 1, ',', '.') ?> <small>L</small></h3>
                        <p>Total de Litros</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tint"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>€<?= number_format($fuelStats['total_value'], 2, ',', '.') ?></h3>
                        <p>Custo Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>€<?= number_format($fuelStats['avg_price_per_liter'], 3, ',', '.') ?></h3>
                        <p>Preço Médio/Litro</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box" style="background-color: var(--primary-color); color: white;">
                    <div class="inner">
                        <h3><?= $fuelStats['total_records'] ?></h3>
                        <p>Abastecimentos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-area mr-2"></i>Evolução Mensal de Consumo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="fuelTrendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Consumo por Veículo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="vehicleConsumptionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Consumo por Veículo -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-car mr-2"></i>Consumo Detalhado por Veículo
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Veículo</th>
                            <th>Marca/Modelo</th>
                            <th class="text-right">Litros</th>
                            <th class="text-right">Custo Total</th>
                            <th class="text-right">Nº Abastecimentos</th>
                            <th class="text-right">Média/Abastecimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fuelByVehicle as $vehicle): ?>
                        <tr>
                            <td>
                                <i class="fas fa-car mr-2 text-primary"></i>
                                <strong><?= Html::encode($vehicle['license_plate']) ?></strong>
                            </td>
                            <td><?= Html::encode($vehicle['brand'] . ' ' . $vehicle['model']) ?></td>
                            <td class="text-right"><?= number_format($vehicle['total_liters'], 2, ',', '.') ?> L</td>
                            <td class="text-right">€<?= number_format($vehicle['total_value'], 2, ',', '.') ?></td>
                            <td class="text-right"><?= $vehicle['refuel_count'] ?></td>
                            <td class="text-right">
                                <?php 
                                $avg = $vehicle['refuel_count'] > 0 
                                    ? $vehicle['total_value'] / $vehicle['refuel_count'] 
                                    : 0;
                                ?>
                                €<?= number_format($avg, 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($fuelByVehicle)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                Nenhum registo de combustível encontrado para o período selecionado.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Últimos Abastecimentos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>Últimos Abastecimentos
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Veículo</th>
                            <th class="text-right">Litros</th>
                            <th class="text-right">Valor</th>
                            <th class="text-right">€/L</th>
                            <th class="text-right">Quilometragem</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentFuelLogs as $log): ?>
                        <tr>
                            <td><?= Yii::$app->formatter->asDate($log->date, 'dd/MM/yyyy') ?></td>
                            <td>
                                <?php if ($log->vehicle): ?>
                                    <i class="fas fa-car mr-1 text-primary"></i>
                                    <?= Html::encode($log->vehicle->license_plate) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right"><?= $log->liters ? number_format($log->liters, 2, ',', '.') : '0' ?> L</td>
                            <td class="text-right">€<?= $log->value ? number_format($log->value, 2, ',', '.') : '0' ?></td>
                            <td class="text-right">€<?= $log->price_per_liter ? number_format($log->price_per_liter, 3, ',', '.') : '-' ?></td>
                            <td class="text-right">
                                <?= $log->current_mileage ? number_format($log->current_mileage, 0, ',', '.') . ' km' : '-' ?>
                            </td>
                            <td>
                                <?= $log->notes ? Html::encode($log->notes) : '<span class="text-muted">-</span>' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentFuelLogs)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                Nenhum abastecimento registado.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
$monthLabelsJs = json_encode($monthLabels);
$monthValuesJs = json_encode(array_map('floatval', $monthValues));
$monthLitersJs = json_encode(array_map('floatval', $monthLiters));
$vehicleLabelsJs = json_encode(array_slice($vehicleLabels, 0, 10));
$vehicleCostsJs = json_encode(array_slice($vehicleCosts, 0, 10));

$js = <<<JS
// Gráfico de Tendência
const trendCtx = document.getElementById('fuelTrendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: {$monthLabelsJs},
        datasets: [
            {
                label: 'Custo (€)',
                data: {$monthValuesJs},
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: true,
                tension: 0.4,
                yAxisID: 'y'
            },
            {
                label: 'Litros',
                data: {$monthLitersJs},
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'transparent',
                borderDash: [5, 5],
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
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

// Gráfico por Veículo
const vehicleCtx = document.getElementById('vehicleConsumptionChart').getContext('2d');
new Chart(vehicleCtx, {
    type: 'doughnut',
    data: {
        labels: {$vehicleLabelsJs},
        datasets: [{
            data: {$vehicleCostsJs},
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(199, 199, 199, 0.8)',
                'rgba(83, 102, 255, 0.8)',
                'rgba(255, 99, 255, 0.8)',
                'rgba(99, 255, 132, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12
                }
            }
        }
    }
});
JS;

$this->registerJs($js);
?>
