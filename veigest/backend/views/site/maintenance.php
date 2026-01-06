<?php

/** @var yii\web\View $this */

$this->title = 'Manutenção';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manutenção - VeiGest</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
                        <a href="maintenance.html" class="nav-link active">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Manutenção</p>
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
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Plano de Manutenção</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addMaintenanceModal">
                            <i class="fas fa-plus mr-2"></i>Agendar Manutenção
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <section class="content">
            <div class="container-fluid">
                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: #17a2b8;">
                            <div class="inner text-white">
                                <h3>12</h3>
                                <p>Agendadas</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: var(--primary-color);">
                            <div class="inner">
                                <h3>8</h3>
                                <p>Em Progresso</p>
                            </div>
                            <div class="icon"><i class="fas fa-spinner"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: #28a745;">
                            <div class="inner text-white">
                                <h3>95</h3>
                                <p>Concluídas</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box" style="background-color: #ffc107;">
                            <div class="inner">
                                <h3>3</h3>
                                <p>Atrasos</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Histórico de Manutenção</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Veículo</th>
                                    <th>Tipo</th>
                                    <th>Data Programada</th>
                                    <th>Custo Estimado</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A-123-AB</td>
                                    <td>Troca de óleo</td>
                                    <td>25/11/2024</td>
                                    <td>€150.00</td>
                                    <td><span class="badge badge-info">Agendada</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>B-456-BC</td>
                                    <td>Revisão completa</td>
                                    <td>20/11/2024</td>
                                    <td>€500.00</td>
                                    <td><span class="badge badge-success">Concluída</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="addMaintenanceModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                    <h4 class="modal-title">Agendar Manutenção</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Veículo</label>
                            <select class="form-control">
                                <option>Selecione um veículo...</option>
                                <option>A-123-AB</option>
                                <option>B-456-BC</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Manutenção</label>
                            <select class="form-control">
                                <option>Selecione...</option>
                                <option>Troca de óleo</option>
                                <option>Revisão completa</option>
                                <option>Pneus</option>
                                <option>Freios</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Data</label>
                            <input type="date" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Custo Estimado</label>
                            <input type="number" class="form-control" placeholder="0.00" step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agendar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="main-footer">
        <strong>VeiGest &copy; 2025</strong>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
