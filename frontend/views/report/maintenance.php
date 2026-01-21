<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\models\Maintenance;
use frontend\controllers\ReportController;

/** @var yii\web\View $this */
/** @var array $maintenanceStats */
/** @var array $maintenanceMonthly */
/** @var array $maintenanceByType */
/** @var array $maintenanceByVehicle */
/** @var array $upcomingMaintenance */
/** @var common\models\Maintenance[] $recentMaintenance */
/** @var common\models\Vehicle[] $vehicles */
/** @var string $period */
/** @var int|null $vehicleId */

$this->title = 'Relatório de Manutenções';
$this->params['breadcrumbs'][] = ['label' => 'Relatórios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Preparar dados para gráficos
$monthLabels = array_column($maintenanceMonthly, 'month_label');
$monthValues = array_column($maintenanceMonthly, 'total_cost');
$monthCounts = array_column($maintenanceMonthly, 'count');

$typeLabels = array_map(function($t) {
    return Maintenance::getTypeLabels()[$t['type']] ?? $t['type'];
}, $maintenanceByType);
$typeCosts = array_column($maintenanceByType, 'total_cost');

$vehicleLabels = array_column($maintenanceByVehicle, 'license_plate');
$vehicleCosts = array_column($maintenanceByVehicle, 'total_cost');
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-tools mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Voltar', ['index'], [
                    'class' => 'btn btn-secondary'
                ]) ?>
                <?= Html::a('<i class="fas fa-download mr-1"></i>Exportar CSV', 
                    ['export-csv', 'type' => 'maintenance', 'period' => $period], 
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
                <?= Html::beginForm(Url::to(['report/maintenance'], true), 'get') ?>
                <?= Html::hiddenInput('r', 'report/maintenance') ?>
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
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>€<?= number_format($maintenanceStats['total_cost'], 2, ',', '.') ?></h3>
                        <p>Custo Total</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                </div>
            </div>
                <div class="col-lg-3 col-md-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $maintenanceStats['total_records'] ?></h3>
                            <p>Total Manutenções</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-wrench"></i>
                        </div>
                    </div>
                </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>€<?= number_format($maintenanceStats['avg_cost'], 2, ',', '.') ?></h3>
                        <p>Custo Médio</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= count($upcomingMaintenance) ?></h3>
                        <p>Agendadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
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
                            <i class="fas fa-chart-bar mr-2"></i>Custos Mensais
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 320px;">
                            <canvas id="monthlyCostChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-2"></i>Custos por Tipo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 320px;">
                            <canvas id="typeCostChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-car mr-2"></i>Custos por Veículo
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 300px;">
                            <canvas id="vehicleCostChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manutenções Agendadas -->
        <?php if (!empty($upcomingMaintenance)): ?>
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt mr-2"></i>Manutenções Agendadas
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data Agendada</th>
                            <th>Veículo</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th class="text-right">Custo Estimado</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcomingMaintenance as $maintenance): ?>
                        <tr>
                            <td>
                                <?php 
                                $date = strtotime($maintenance['next_date']);
                                $daysUntil = ceil(($date - time()) / 86400);
                                $badgeClass = $daysUntil <= 7 ? 'badge-danger' : ($daysUntil <= 14 ? 'badge-warning' : 'badge-info');
                                ?>
                                <?= Yii::$app->formatter->asDate($maintenance['next_date'], 'dd/MM/yyyy') ?>
                                <span class="badge <?= $badgeClass ?>"><?= $daysUntil ?> dias</span>
                            </td>
                            <td>
                                <i class="fas fa-car mr-1 text-primary"></i>
                                <?= Html::encode($maintenance['license_plate']) ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?= Html::encode(Maintenance::getTypeLabels()[$maintenance['type']] ?? $maintenance['type']) ?>
                                </span>
                            </td>
                            <td><?= Html::encode($maintenance['description']) ?></td>
                            <td class="text-right">
                                <?= $maintenance['cost'] ? '€' . number_format($maintenance['cost'], 2, ',', '.') : '-' ?>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-warning">Agendado</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela de Custos por Tipo -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list mr-2"></i>Resumo por Tipo de Manutenção
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th class="text-right">Quantidade</th>
                                    <th class="text-right">Custo Total</th>
                                    <th class="text-right">Média</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maintenanceByType as $type): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-tag mr-2 text-secondary"></i>
                                        <?= Html::encode(Maintenance::getTypeLabels()[$type['type']] ?? $type['type']) ?>
                                    </td>
                                    <td class="text-right"><?= $type['count'] ?></td>
                                    <td class="text-right">€<?= number_format($type['total_cost'], 2, ',', '.') ?></td>
                                    <td class="text-right">€<?= number_format($type['avg_cost'], 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($maintenanceByType)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Nenhum dado disponível
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
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-car-side mr-2"></i>Custos por Veículo
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Veículo</th>
                                    <th class="text-right">Manutenções</th>
                                    <th class="text-right">Custo Total</th>
                                    <th class="text-right">Custo Médio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($maintenanceByVehicle as $vehicle): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-car mr-2 text-primary"></i>
                                        <?= Html::encode($vehicle['license_plate']) ?>
                                    </td>
                                    <td class="text-right"><?= $vehicle['maintenance_count'] ?></td>
                                    <td class="text-right">€<?= number_format($vehicle['total_cost'], 2, ',', '.') ?></td>
                                    <td class="text-right">€<?= number_format($vehicle['avg_cost'], 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($maintenanceByVehicle)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Nenhum dado disponível
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimas Manutenções -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>Últimas Manutenções
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Veículo</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th class="text-right">Custo</th>
                            <th class="text-right">Quilometragem</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentMaintenance as $maintenance): ?>
                        <tr>
                            <td><?= Yii::$app->formatter->asDate($maintenance->date, 'dd/MM/yyyy') ?></td>
                            <td>
                                <?php if ($maintenance->vehicle): ?>
                                    <i class="fas fa-car mr-1 text-primary"></i>
                                    <?= Html::encode($maintenance->vehicle->license_plate) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?= Html::encode(Maintenance::getTypeLabels()[$maintenance->type] ?? $maintenance->type) ?>
                                </span>
                            </td>
                            <td><?= Html::encode($maintenance->description) ?></td>
                            <td class="text-right">
                                <?= $maintenance->cost ? '€' . number_format($maintenance->cost, 2, ',', '.') : '-' ?>
                            </td>
                            <td class="text-right">
                                <?= $maintenance->mileage_record ? number_format($maintenance->mileage_record, 0, ',', '.') . ' km' : '-' ?>
                            </td>
                            <td class="text-center">
                                <?php
                                // Determinar status baseado na data
                                $today = date('Y-m-d');
                                if ($maintenance->date > $today) {
                                    $statusClass = 'warning';
                                    $statusLabel = 'Agendado';
                                } else {
                                    $statusClass = 'success';
                                    $statusLabel = 'Concluído';
                                }
                                ?>
                                <span class="badge badge-<?= $statusClass ?>"><?= $statusLabel ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentMaintenance)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle mr-2"></i>
                                Nenhuma manutenção registada.
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
$monthCountsJs = json_encode(array_map('intval', $monthCounts));
$typeLabelsJs = json_encode($typeLabels);
$typeCostsJs = json_encode(array_map('floatval', $typeCosts));
$vehicleLabelsJs = json_encode(array_slice($vehicleLabels, 0, 10));
$vehicleCostsJs = json_encode(array_map('floatval', array_slice($vehicleCosts, 0, 10)));

$js = <<<JS
// Gráfico de Custos Mensais
const monthlyCtx = document.getElementById('monthlyCostChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: {$monthLabelsJs},
        datasets: [{
            label: 'Custo (€)',
            data: {$monthValuesJs},
            backgroundColor: 'rgba(220, 53, 69, 0.8)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
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

// Gráfico por Tipo
const typeCtx = document.getElementById('typeCostChart').getContext('2d');
new Chart(typeCtx, {
    type: 'doughnut',
    data: {
        labels: {$typeLabelsJs},
        datasets: [{
            data: {$typeCostsJs},
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
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': €' + context.parsed.toLocaleString('pt-PT', {minimumFractionDigits: 2});
                    }
                }
            }
        }
    }
});

// Gráfico por Veículo
const vehicleCtx = document.getElementById('vehicleCostChart').getContext('2d');
new Chart(vehicleCtx, {
    type: 'bar',
    data: {
        labels: {$vehicleLabelsJs},
        datasets: [{
            label: 'Custo Total (€)',
            data: {$vehicleCostsJs},
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
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
JS;

$this->registerJs($js);
?>
