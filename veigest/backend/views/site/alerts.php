<?php

/** @var yii\web\View $this */

$this->title = 'Alertas';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas - VeiGest</title>
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #09BC8A;
            --dark-color: #3C3C3C;
            --light-turquoise: #75DDDD;
            --lavender-gray: #C8BFC7;
            --lavender-blush: #FFEAEE;
        }
        
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        .navbar-light .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .main-sidebar .nav-link.active {
            background-color: var(--primary-color) !important;
            color: white !important;
        }
        
        .nav-link {
            color: #666 !important;
        }
        
        .nav-link:hover {
            background-color: rgba(9, 188, 138, 0.1) !important;
            color: var(--primary-color) !important;
        }
        
        .brand-link {
            background-color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        
        .btn-primary:hover {
            background-color: #088570 !important;
        }
        
        .badge-success {
            background-color: var(--primary-color) !important;
        }
        
        .card {
            border-top: 3px solid var(--primary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .rbac-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .rbac-admin { background-color: #dc3545; color: white; }
        .rbac-gestor { background-color: #ffc107; color: #333; }
        .rbac-condutor { background-color: #17a2b8; color: white; }
        .rbac-convidado { background-color: #6c757d; color: white; }
        
        .alert-item { border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 10px; background-color: #f9f9f9; }
        .alert-critica { border-left-color: #dc3545; }
        .alert-alta { border-left-color: #ffc107; }
        .alert-media { border-left-color: #17a2b8; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark" style="background-color: var(--dark-color);">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="dashboard.html" class="nav-link">Home</a>
            </li>
        </ul>
        
        <!-- Right navbar -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-bell"></i>
                    <span class="badge badge-danger navbar-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-header">3 Notificações</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-warning text-warning mr-2"></i> Manutenção programada
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-exclamation-triangle text-danger mr-2"></i> Alerta crítico
                    </a>
                </div>
            </li>
            
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="profile.html" class="dropdown-item"><i class="fas fa-user mr-2"></i> Perfil</a>
                    <div class="dropdown-divider"></div>
                    <a href="login.html" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i> Sair</a>
                </div>
            </li>
        </ul>
    </nav>
    
    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #f8f9fa;">
        <a href="dashboard.html" class="brand-link d-flex align-items-center">
            <img src="/images/veigest-logo.png" 
                 alt="VeiGest" style="width: 35px; height: 35px; margin-right: 10px;">
            <span style="color: white; font-weight: 700;">VeiGest</span>
        </a>
        
        <div class="sidebar">
            <!-- User Panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://via.placeholder.com/160x160?text=Admin" class="img-circle elevation-2" alt="Admin">
                </div>
                <div class="info">
                    <a href="#" class="d-block" style="color: var(--dark-color); font-weight: 600;">Admin</a>
                    <span class="rbac-badge rbac-admin">Admin</span>
                </div>
            </div>
            
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.html" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    
                    <li class="nav-item menu-open">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-car"></i>
                            <p>Frota <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="vehicles.html" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Veículos</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="drivers.html" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Condutores</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a href="maintenance.html" class="nav-link">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Manutenção</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="documents.html" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Documentos</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="alerts.html" class="nav-link active">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>Alertas</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="reports.html" class="nav-link">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Relatórios</p>
                        </a>
                    </li>
                    
                    <!-- Admin Section -->
                    <li class="nav-header" style="color: #999;">ADMINISTRAÇÃO</li>
                    
                    <li class="nav-item">
                        <a href="users.html" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Gestão de Utilizadores</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="system-settings.html" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Configurações do Sistema</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="logs.html" class="nav-link">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Registos</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    
    <!-- Content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Alertas do Sistema</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.html">Home</a></li>
                            <li class="breadcrumb-item active">Alertas</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="mb-3">
                    <button class="btn btn-danger">Críticos (3)</button>
                    <button class="btn btn-warning">Altos (4)</button>
                    <button class="btn btn-info">Médios (5)</button>
                    <button class="btn btn-success">Resolvidos (45)</button>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Alertas Ativos</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert-item alert-critica">
                            <h5><i class="fas fa-exclamation-circle mr-2"></i>Documentação Expirada</h5>
                            <p>Seguro do veículo ABC-1234 expirou a 15/10/2025</p>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-check mr-1"></i>Resolver</button>
                        </div>
                        
                        <div class="alert-item alert-critica">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Inspeção Periódica Vencida</h5>
                            <p>Inspeção do veículo DEF-5678 venceu a 30/09/2025</p>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-check mr-1"></i>Resolver</button>
                        </div>
                        
                        <div class="alert-item alert-alta">
                            <h5><i class="fas fa-calendar mr-2"></i>Manutenção Programada Próxima</h5>
                            <p>Revisão do veículo XYZ-9012 vence em 3 dias (10/11/2025)</p>
                            <button class="btn btn-sm btn-warning"><i class="fas fa-calendar mr-1"></i>Agendar</button>
                        </div>
                        
                        <div class="alert-item alert-media">
                            <h5><i class="fas fa-info-circle mr-2"></i>Consumo de Combustível Anormal</h5>
                            <p>Veículo GHI-3456 apresenta consumo 18% acima da média esperada</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Footer -->
    <footer class="main-footer">
        <strong>VeiGest &copy; 2025</strong>
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
