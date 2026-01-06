<?php

/** @var yii\web\View $this */

$this->title = 'Relatórios';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - VeiGest</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <style>
        :root {
            --primary-color: #09BC8A;
            --dark-color: #3C3C3C;
        }
        
        * { font-family: 'Poppins', sans-serif; }
        .navbar-light .navbar-brand { color: white !important; font-weight: 700; }
        .main-sidebar .nav-link.active { background-color: var(--primary-color) !important; color: white !important; }
        .brand-link { background-color: var(--primary-color) !important; }
        .btn-primary { background-color: var(--primary-color) !important; border-color: var(--primary-color) !important; }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-dark" style="background-color: var(--dark-color);">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle"></i>
                </a>
            </li>
        </ul>
    </nav>
    
    <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #f8f9fa;">
        <a href="dashboard.html" class="brand-link d-flex align-items-center">
            <img src="/images/veigest-logo.png" 
                 alt="VeiGest" style="width: 35px; height: 35px; margin-right: 10px;">
            <span style="color: white; font-weight: 700;">VeiGest</span>
        </a>
        
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.html" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.html" class="nav-link active">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Relatórios</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    
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
    
    <footer class="main-footer">
        <strong>VeiGest &copy; 2025</strong>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' }
        }
    };
    
    const ctxConsumo = document.getElementById('chartConsumo').getContext('2d');
    new Chart(ctxConsumo, {
        type: 'line',
        data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            datasets: [{
                label: 'Consumo (L)',
                data: [1200, 1400, 1600, 1300, 1500, 1800],
                borderColor: '#09BC8A',
                backgroundColor: 'rgba(9, 188, 138, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: chartOptions
    });
    
    const ctxCustos = document.getElementById('chartCustos').getContext('2d');
    new Chart(ctxCustos, {
        type: 'doughnut',
        data: {
            labels: ['Combustível', 'Manutenção', 'Seguros', 'Outros'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: ['#09BC8A', '#17a2b8', '#ffc107', '#6c757d']
            }]
        },
        options: chartOptions
    });
</script>
</body>
</html>
