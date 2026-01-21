<?php

/** @var yii\web\View $this */

$this->title = 'Frota de Veículos';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frota de Veículos - VeiGest</title>
    
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
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="login.html" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
                </div>
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
                        <a href="vehicles.html" class="nav-link active">
                            <i class="nav-icon fas fa-car"></i>
                            <p>Veículos</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="maintenance.html" class="nav-link">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Manutenção</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports.html" class="nav-link">
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
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Gestão de Frota</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addVehicleModal">
                            <i class="fas fa-plus mr-2"></i>Novo Veículo
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Veículos Registados</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Marca/Modelo</th>
                                    <th>Condutor Atual</th>
                                    <th>Manutenção</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A-123-AB</td>
                                    <td>Mercedes C-Class</td>
                                    <td>João Carlos</td>
                                    <td><span class="badge badge-success">Em Dia</span></td>
                                    <td><span class="badge badge-success">Ativo</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>B-456-BC</td>
                                    <td>BMW 3 Series</td>
                                    <td>Maria Silva</td>
                                    <td><span class="badge badge-warning">Próximo: 30 dias</span></td>
                                    <td><span class="badge badge-success">Ativo</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
    <div class="modal fade" id="addVehicleModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                    <h4 class="modal-title">Novo Veículo</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Matrícula</label>
                                    <input type="text" class="form-control" placeholder="Ex: XX-XX-XX">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>VIN</label>
                                    <input type="text" class="form-control" placeholder="Número de chassis">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Marca</label>
                                    <input type="text" class="form-control" placeholder="Mercedes, BMW, etc">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Modelo</label>
                                    <input type="text" class="form-control" placeholder="Modelo do veículo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar Veículo</button>
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
