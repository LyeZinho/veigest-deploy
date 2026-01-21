<?php

use frontend\models\Vehicle;
use frontend\models\Driver;
use frontend\models\Maintenance;

/** @var \yii\web\View $this */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$companyId = \Yii::$app->user->identity->company_id;

// Get real data
$totalVehicles = Vehicle::find()->where(['company_id' => $companyId])->count();
$totalDrivers = Driver::find()
    ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
    ->where([
        'auth_assignment.item_name' => 'driver',
        'users.company_id' => $companyId,
        'users.status' => Driver::STATUS_ACTIVE
    ])->count();

// Count alerts
$today = date('Y-m-d');
$expiredDocs = \Yii::$app->db->createCommand(
    'SELECT COUNT(*) FROM documents WHERE company_id = :cid AND expiry_date < :today',
    [':cid' => $companyId, ':today' => $today]
)->queryScalar();
$overdueMaint = Maintenance::find()
    ->where(['company_id' => $companyId, 'status' => 'scheduled'])
    ->andWhere(['<', 'date', $today])
    ->count();
$totalAlerts = (int)$expiredDocs + (int)$overdueMaint;

// Total cost of completed maintenances
$monthlyCost = (float)\Yii::$app->db->createCommand(
    'SELECT COALESCE(SUM(cost), 0) FROM maintenances WHERE company_id = :cid AND status = "completed"',
    [':cid' => $companyId]
)->queryScalar();
?>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $totalVehicles ?></h3>
                <p>Total Veículos</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
            <a href="<?= \yii\helpers\Url::to(['vehicle/index']) ?>" class="small-box-footer">Ver mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $totalDrivers ?></h3>
                <p>Condutores Ativos</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <a href="<?= \yii\helpers\Url::to(['driver/index']) ?>" class="small-box-footer">Ver mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 style="color: white;"><?= $totalAlerts ?></h3>
                <p style="color: white;">Alertas Pendentes</p>
            </div>
            <div class="icon">
                <i class="fas fa-bell"></i>
            </div>
            <a href="<?= \yii\helpers\Url::to(['alert/index']) ?>" class="small-box-footer">Ver mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 style="color: white;">€<?= number_format($monthlyCost, 2, ',', '.') ?></h3>
                <p style="color: white;">Custo Total</p>
            </div>
            <div class="icon">
                <i class="fas fa-euro-sign"></i>
            </div>
            <a href="<?= \yii\helpers\Url::to(['maintenance/index', 'status' => 'completed']) ?>" class="small-box-footer">Ver mais <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manutenções por Status</h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="stateChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Custos Mensais (últimos 6 meses)</h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="costChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Ações Rápidas</h3>
            </div>
            <div class="card-body">
                <?php if (\Yii::$app->user->can('vehicles.create')): ?>
                    <a href="<?= \yii\helpers\Url::to(['vehicle/create']) ?>" class="btn btn-primary btn-sm me-2"><i class="fas fa-plus"></i> Novo Veículo</a>
                <?php endif; ?>
                <?php if (\Yii::$app->user->can('drivers.create')): ?>
                    <a href="<?= \yii\helpers\Url::to(['driver/create']) ?>" class="btn btn-primary btn-sm me-2"><i class="fas fa-plus"></i> Novo Condutor</a>
                <?php endif; ?>
                <?php if (\Yii::$app->user->can('maintenances.create')): ?>
                    <a href="<?= \yii\helpers\Url::to(['maintenance/create']) ?>" class="btn btn-primary btn-sm me-2"><i class="fas fa-plus"></i> Agendar Manutenção</a>
                <?php endif; ?>
                <?php if (\Yii::$app->user->can('documents.create')): ?>
                    <a href="<?= \yii\helpers\Url::to(['document/create']) ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Novo Documento</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Cards -->
<div class="row mt-4 mb-4 g-3">
    <div class="col-md-4">
        <a href="<?= \yii\helpers\Url::to(['maintenance/index', 'status' => 'scheduled']) ?>" class="card text-decoration-none text-dark h-100 hover-shadow" style="min-height: 140px;">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h5 class="card-title mb-0" style="font-size: 18px; font-weight: 700; color: #2c3e50;">Manutenções Agendadas</h5>
                </div>
                <div class="text-end">
                    <i class="fas fa-wrench fa-3x text-primary" style="opacity: 0.35;"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= \yii\helpers\Url::to(['maintenance/index', 'status' => 'overdue']) ?>" class="card text-decoration-none text-dark h-100 hover-shadow" style="min-height: 140px;">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h5 class="card-title mb-0" style="font-size: 18px; font-weight: 700; color: #2c3e50;">Manutenções Atrasadas</h5>
                </div>
                <div class="text-end">
                    <i class="fas fa-exclamation-triangle fa-3x text-danger" style="opacity: 0.35;"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= \yii\helpers\Url::to(['alert/index']) ?>" class="card text-decoration-none text-dark h-100 hover-shadow" style="min-height: 140px;">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <h5 class="card-title mb-0" style="font-size: 18px; font-weight: 700; color: #2c3e50;">Alertas & Documentos</h5>
                </div>
                <div class="text-end">
                    <i class="fas fa-bell fa-3x text-warning" style="opacity: 0.35;"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
    border: 1px solid #e3e6f0;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
    border-color: #d1d3d8;
}

.card-title {
    color: #2c3e50;
    line-height: 1.4;
    word-break: break-word;
}

.text-muted {
    color: #6c757d !important;
}
</style>

<script>
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' }
    }
};

<?php
// Maintenance stats
$scheduled = Maintenance::find()
    ->where(['company_id' => $companyId, 'status' => 'scheduled'])
    ->andWhere(['>', 'date', $today])
    ->count();
$completed = Maintenance::find()
    ->where(['company_id' => $companyId, 'status' => 'completed'])
    ->count();
$overdue = Maintenance::find()
    ->where(['company_id' => $companyId, 'status' => 'scheduled'])
    ->andWhere(['<', 'date', $today])
    ->count();

// Monthly costs (last 6 months)
$monthlyCosts = [];
$labels = [];
for ($i = 5; $i >= 0; $i--) {
    $monthDate = date('Y-m', strtotime("-$i month"));
    $monthStart = $monthDate . '-01';
    $monthEnd = date('Y-m-t', strtotime($monthStart));
    
    // Use updated_at for completed maintenances (when they were actually completed)
    $cost = (float)\Yii::$app->db->createCommand(
        'SELECT COALESCE(SUM(cost), 0) FROM maintenances WHERE company_id = :cid AND status = "completed" AND DATE(updated_at) BETWEEN :start AND :end',
        [':cid' => $companyId, ':start' => $monthStart, ':end' => $monthEnd]
    )->queryScalar();
    
    $monthlyCosts[] = $cost;
    $labels[] = date('M', strtotime($monthStart));
}
?>

// State Distribution Chart (Maintenance Status)
const ctxState = document.getElementById('stateChart').getContext('2d');
new Chart(ctxState, {
    type: 'doughnut',
    data: {
        labels: ['Agendadas', 'Concluidas', 'Atrasadas'],
        datasets: [{
            data: [<?= $scheduled ?>, <?= $completed ?>, <?= $overdue ?>],
            backgroundColor: ['#09BC8A', '#17A2B8', '#EF4444']
        }]
    },
    options: chartOptions
});

// Monthly Cost Chart
const ctxCost = document.getElementById('costChart').getContext('2d');
new Chart(ctxCost, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Custo (EUR)',
            data: <?= json_encode($monthlyCosts) ?>,
            backgroundColor: '#09BC8A',
            borderColor: '#08a572',
            borderWidth: 1
        }]
    },
    options: chartOptions
});
</script>