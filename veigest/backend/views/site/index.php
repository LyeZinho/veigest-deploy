<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Dashboard';

$identity = Yii::$app->user->identity;
$displayName = !empty($identity->name) ? $identity->name : (!empty($identity->username) ? $identity->username : 'Utilizador');

?>
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Dashboard Administrativo</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPI CARDS -->
        <div class="row mb-4">

            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $totalUsers ?></h3><p>Total Utilizadores</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3><?= $totalCompanies ?></h3><p>Empresas</p>
                    </div>
                    <div class="icon"><i class="fas fa-building"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $totalVehicles ?></h3><p>Total Veículos</p>
                    </div>
                    <div class="icon"><i class="fas fa-truck"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 style="color:white;"><?= $maintenanceAlerts ?></h3>
                        <p style="color:white;">Manutenções Atrasadas</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>

        </div>

        <!-- CHARTS -->
        <div class="row">

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Consumo de Combustível (Últimos 6 meses)</h3></div>
                    <div class="card-body" style="height: 300px;"><canvas id="consumptionChart"></canvas></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Estado da Frota</h3></div>
                    <div class="card-body" style="height: 300px;"><canvas id="stateChart"></canvas></div>
                </div>
            </div>

        </div>

        <!-- ESTATÍSTICAS -->
        <div class="row mt-4">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Resumo de Veículos</h3></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div style="text-align:center;padding:15px;border-right:1px solid #ddd;">
                                    <h5 style="font-size:24px;color:#09BC8A;"><?= $activeVehicles ?></h5>
                                    <p class="text-muted">Veículos Ativos</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="text-align:center;padding:15px;">
                                    <h5 style="font-size:24px;color:#f59e0b;"><?= $totalVehicles - $activeVehicles ?></h5>
                                    <p class="text-muted">Veículos Inativos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Informações do Sistema</h3></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Versão:</strong></td>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <td><strong>Data Atual:</strong></td>
                                <td><?= date('d/m/Y H:i:s') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Utilizador:</strong></td>
                                <td><?= Html::encode($displayName) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Framework:</strong></td>
                                <td>Yii2</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- CHART.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
    const chartOptions = { responsive:true, maintainAspectRatio:false };

    // Consumo de combustível
    const ctxC = document.getElementById('consumptionChart')?.getContext('2d');
    if (ctxC) {
        new Chart(ctxC, {
            type:'line',
            data:{
                labels: <?= $fuelLabels ?>,
                datasets:[{
                    label:'Combustível (L)',
                    data: <?= $fuelValues ?>,
                    borderColor:'#09BC8A',
                    backgroundColor:'rgba(9,188,138,0.1)',
                    fill:true,
                    tension:0.4,
                    borderWidth:2
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{
                    legend:{
                        display:true,
                        position:'top'
                    }
                },
                scales:{
                    y:{
                        beginAtZero:true
                    }
                }
            }
        });
    }

    // Estado da frota
    const ctxS = document.getElementById('stateChart')?.getContext('2d');
    if (ctxS) {
        new Chart(ctxS, {
            type:'doughnut',
            data:{
                labels: <?= $statusLabels ?>,
                datasets:[{ 
                    data: <?= $statusValues ?>,
                    backgroundColor:['#09BC8A','#EF4444','#F59E0B']
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{
                    legend:{
                        position:'bottom'
                    }
                }
            }
        });
    }
</script>
