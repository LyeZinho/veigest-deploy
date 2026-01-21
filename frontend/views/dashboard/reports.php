<?php

/** @var yii\web\View $this */

$this->title = 'Relatórios Operacionais';
?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Relatórios Operacionais</h1>
                    </div>
                </div>
            </div>
        </div>
        
        <section class="content">
            <div class="container-fluid">
                <!-- Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Período</label>
                                <select class="form-control">
                                    <option>Este Mês</option>
                                    <option>Últimos 3 Meses</option>
                                    <option>Últimos 6 Meses</option>
                                    <option>Este Ano</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Veículo</label>
                                <select class="form-control">
                                    <option>Todos</option>
                                    <option>A-123-AB</option>
                                    <option>B-456-BC</option>
                                </select>
                            </div>
                            <div class="col-md-4 pt-4">
                                <button class="btn btn-primary btn-block"><i class="fas fa-filter mr-2"></i>Aplicar Filtro</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Consumo Mensal</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartConsumo"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Custos por Categoria</h3>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartCustos"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Resumo Operacional</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Métrica</th>
                                    <th>Valor</th>
                                    <th>Variação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total de Km</td>
                                    <td>125.430 km</td>
                                    <td><span class="badge badge-success">+15%</span></td>
                                </tr>
                                <tr>
                                    <td>Consumo Médio</td>
                                    <td>7.2 L/100km</td>
                                    <td><span class="badge badge-danger">-5%</span></td>
                                </tr>
                                <tr>
                                    <td>Custos Totais</td>
                                    <td>€12.450</td>
                                    <td><span class="badge badge-info">+8%</span></td>
                                </tr>
                                <tr>
                                    <td>Tempo em Viagem</td>
                                    <td>385h</td>
                                    <td><span class="badge badge-success">+3%</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>