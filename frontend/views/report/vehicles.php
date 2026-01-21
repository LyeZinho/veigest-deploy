<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\models\Maintenance;
use frontend\controllers\ReportController;

/** @var yii\web\View $this */
/** @var array $vehiclesAnalysis */
/** @var string $period */

$this->title = 'Análise de Veículos';
$this->params['breadcrumbs'][] = ['label' => 'Relatórios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Preparar dados para gráficos
$vehicleLabels = array_column($vehiclesAnalysis, 'license_plate');
$totalCosts = array_map(function($v) {
    return (float)$v['total_cost'];
}, $vehiclesAnalysis);
$fuelCosts = array_map(function($v) {
    return (float)$v['fuel_cost'];
}, $vehiclesAnalysis);
$maintenanceCosts = array_map(function($v) {
    return (float)$v['maintenance_cost'];
}, $vehiclesAnalysis);
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-car mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Voltar', ['index'], [
                    'class' => 'btn btn-secondary'
                ]) ?>
                <?= Html::a('<i class="fas fa-download mr-1"></i>Exportar CSV', 
                    ['export-csv', 'type' => 'vehicles', 'period' => $period], 
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
                <?= Html::beginForm(Url::to(['report/vehicles'], true), 'get') ?>
                <?= Html::hiddenInput('r', 'report/vehicles') ?>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Período</label>
                            <?= Html::dropDownList('period', $period, ReportController::getPeriodOptions(), [
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-6 pt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter mr-2"></i>Aplicar Filtro
                            </button>
                        </div>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <!-- Resumo Geral -->
        <?php
        $totalVehicles = count($vehiclesAnalysis);
        $totalFuelCost = array_sum(array_column($vehiclesAnalysis, 'fuel_cost'));
        $totalMaintenanceCost = array_sum(array_column($vehiclesAnalysis, 'maintenance_cost'));
        $totalCostSum = array_sum(array_column($vehiclesAnalysis, 'total_cost'));
        ?>
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="small-box" style="background-color: var(--primary-color); color: white;">
                    <div class="inner">
                        <h3><?= $totalVehicles ?></h3>
                        <p>Veículos Analisados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-car"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>€<?= number_format($totalFuelCost, 0, ',', '.') ?></h3>
                        <p>Total Combustível</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-gas-pump"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>€<?= number_format($totalMaintenanceCost, 0, ',', '.') ?></h3>
                        <p>Total Manutenções</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>€<?= number_format($totalCostSum, 0, ',', '.') ?></h3>
                        <p>Custo Total Frota</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Custo Total por Veículo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="totalCostChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Comparativo: Combustível vs Manutenção
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Distribuição de Custos Totais
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="distributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-percentage mr-2"></i>Combustível vs Manutenção (Total)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 350px;">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Detalhada -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-table mr-2"></i>Análise Detalhada por Veículo
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Marca/Modelo</th>
                                <th class="text-right">Combustível</th>
                                <th class="text-right">Manutenção</th>
                                <th class="text-right">Custo Total</th>
                                <th class="text-center">Abastecimentos</th>
                                <th class="text-center">Manutenções</th>
                                <th class="text-center">Documentos</th>
                                <th class="text-center">% do Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiclesAnalysis as $vehicle): ?>
                            <?php 
                            $percentOfTotal = $totalCostSum > 0 
                                ? ($vehicle['total_cost'] / $totalCostSum) * 100 
                                : 0;
                            ?>
                            <tr>
                                <td>
                                    <i class="fas fa-car mr-2 text-primary"></i>
                                    <strong><?= Html::encode($vehicle['license_plate']) ?></strong>
                                </td>
                                <td><?= Html::encode($vehicle['brand'] . ' ' . $vehicle['model']) ?></td>
                                <td class="text-right">
                                    <span class="text-info">
                                        €<?= number_format($vehicle['fuel_cost'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <span class="text-warning">
                                        €<?= number_format($vehicle['maintenance_cost'], 2, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <strong class="text-danger">
                                        €<?= number_format($vehicle['total_cost'], 2, ',', '.') ?>
                                    </strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= $vehicle['fuel_count'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning"><?= $vehicle['maintenance_count'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?= $vehicle['document_count'] ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-primary" 
                                             role="progressbar" 
                                             style="width: <?= $percentOfTotal ?>%">
                                            <?= number_format($percentOfTotal, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($vehiclesAnalysis)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Nenhum veículo com dados para análise no período selecionado.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($vehiclesAnalysis)): ?>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-right">€<?= number_format($totalFuelCost, 2, ',', '.') ?></th>
                                <th class="text-right">€<?= number_format($totalMaintenanceCost, 2, ',', '.') ?></th>
                                <th class="text-right">€<?= number_format($totalCostSum, 2, ',', '.') ?></th>
                                <th class="text-center"><?= array_sum(array_column($vehiclesAnalysis, 'fuel_count')) ?></th>
                                <th class="text-center"><?= array_sum(array_column($vehiclesAnalysis, 'maintenance_count')) ?></th>
                                <th class="text-center"><?= array_sum(array_column($vehiclesAnalysis, 'document_count')) ?></th>
                                <th class="text-center">100%</th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Rankings -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="card-title">
                            <i class="fas fa-sort-amount-up mr-2"></i>Veículos Mais Caros
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <?php 
                                $sortedByCost = $vehiclesAnalysis;
                                usort($sortedByCost, function($a, $b) {
                                    return $b['total_cost'] <=> $a['total_cost'];
                                });
                                foreach (array_slice($sortedByCost, 0, 5) as $index => $vehicle): 
                                ?>
                                <tr>
                                    <td width="40">
                                        <span class="badge badge-<?= $index === 0 ? 'danger' : ($index === 1 ? 'warning' : 'secondary') ?>">
                                            #<?= $index + 1 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-car mr-1 text-primary"></i>
                                        <?= Html::encode($vehicle['license_plate']) ?>
                                    </td>
                                    <td class="text-right">
                                        <strong>€<?= number_format($vehicle['total_cost'], 2, ',', '.') ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($vehiclesAnalysis)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        Sem dados
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title">
                            <i class="fas fa-sort-amount-down mr-2"></i>Veículos Mais Económicos
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <?php 
                                $sortedByLowCost = $vehiclesAnalysis;
                                usort($sortedByLowCost, function($a, $b) {
                                    return $a['total_cost'] <=> $b['total_cost'];
                                });
                                $sortedByLowCost = array_filter($sortedByLowCost, function($v) {
                                    return $v['total_cost'] > 0;
                                });
                                foreach (array_slice($sortedByLowCost, 0, 5) as $index => $vehicle): 
                                ?>
                                <tr>
                                    <td width="40">
                                        <span class="badge badge-<?= $index === 0 ? 'success' : 'secondary' ?>">
                                            #<?= $index + 1 ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-car mr-1 text-primary"></i>
                                        <?= Html::encode($vehicle['license_plate']) ?>
                                    </td>
                                    <td class="text-right">
                                        <strong>€<?= number_format($vehicle['total_cost'], 2, ',', '.') ?></strong>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($sortedByLowCost)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        Sem dados
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$vehicleLabelsJs = json_encode(array_slice($vehicleLabels, 0, 10));
$totalCostsJs = json_encode(array_slice($totalCosts, 0, 10));
$fuelCostsJs = json_encode(array_slice($fuelCosts, 0, 10));
$maintenanceCostsJs = json_encode(array_slice($maintenanceCosts, 0, 10));

$js = <<<JS
// Gráfico de Custo Total
const totalCostCtx = document.getElementById('totalCostChart').getContext('2d');
new Chart(totalCostCtx, {
    type: 'bar',
    data: {
        labels: {$vehicleLabelsJs},
        datasets: [{
            label: 'Custo Total (€)',
            data: {$totalCostsJs},
            backgroundColor: 'rgba(220, 53, 69, 0.8)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '€' + value.toLocaleString('pt-PT');
                    }
                }
            }
        }
    }
});

// Gráfico Comparativo
const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
new Chart(comparisonCtx, {
    type: 'bar',
    data: {
        labels: {$vehicleLabelsJs},
        datasets: [
            {
                label: 'Combustível (€)',
                data: {$fuelCostsJs},
                backgroundColor: 'rgba(23, 162, 184, 0.8)',
                borderColor: 'rgba(23, 162, 184, 1)',
                borderWidth: 1
            },
            {
                label: 'Manutenção (€)',
                data: {$maintenanceCostsJs},
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '€' + value.toLocaleString('pt-PT');
                    }
                }
            }
        }
    }
});

// Gráfico de Distribuição
const distributionCtx = document.getElementById('distributionChart').getContext('2d');
new Chart(distributionCtx, {
    type: 'pie',
    data: {
        labels: {$vehicleLabelsJs},
        datasets: [{
            data: {$totalCostsJs},
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
                labels: { boxWidth: 12 }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': €' + context.parsed.toLocaleString('pt-PT', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Gráfico de Categorias
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const totalFuel = {$fuelCostsJs}.reduce((a, b) => a + b, 0);
const totalMaintenance = {$maintenanceCostsJs}.reduce((a, b) => a + b, 0);
new Chart(categoryCtx, {
    type: 'doughnut',
    data: {
        labels: ['Combustível', 'Manutenção'],
        datasets: [{
            data: [totalFuel, totalMaintenance],
            backgroundColor: [
                'rgba(23, 162, 184, 0.8)',
                'rgba(255, 193, 7, 0.8)'
            ],
            borderColor: [
                'rgba(23, 162, 184, 1)',
                'rgba(255, 193, 7, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': €' + context.parsed.toLocaleString('pt-PT', {minimumFractionDigits: 2}) + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
JS;

$this->registerJs($js);
?>
