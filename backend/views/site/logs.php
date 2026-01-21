<?php

/** @var yii\web\View $this */

$this->title = 'Registos de Auditoria';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registos de Auditoria - VeiGest</title>
    
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
        
        .log-create { color: #10B981; }
        .log-update { color: #3B82F6; }
        .log-delete { color: #EF4444; }
        .log-view { color: #8B5CF6; }
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
        
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> Perfil
                    </a>
                    <a href="system-settings.html" class="dropdown-item">
                        <i class="fas fa-cog mr-2"></i> Configurações
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="login.html" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sair
                    </a>
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
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://via.placeholder.com/160x160?text=User" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block" style="color: var(--dark-color); font-weight: 600;">Admin</a>
                    <span class="rbac-badge rbac-admin">Admin</span>
                </div>
            </div>
            
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
                        <a href="alerts.html" class="nav-link">
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
                        <a href="logs.html" class="nav-link active">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Registos de Auditoria</p>
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
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Registos de Auditoria</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.html">Home</a></li>
                            <li class="breadcrumb-item">Administração</li>
                            <li class="breadcrumb-item active">Registos</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Filters -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filtros</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Tipo de Ação</label>
                                <select class="form-control">
                                    <option>Todas as ações</option>
                                    <option>Criação (CREATE)</option>
                                    <option>Modificação (UPDATE)</option>
                                    <option>Eliminação (DELETE)</option>
                                    <option>Visualização (VIEW)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Utilizador</label>
                                <input type="text" class="form-control" placeholder="Procurar utilizador">
                            </div>
                            <div class="col-md-3">
                                <label>Data de Início</label>
                                <input type="date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Data de Fim</label>
                                <input type="date" class="form-control">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">
                                <i class="fas fa-search mr-2"></i> Pesquisar
                            </button>
                            <button class="btn btn-secondary">
                                <i class="fas fa-redo mr-2"></i> Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Últimos Registos de Auditoria</h3>
                    </div>
                    <div class="card-body" style="overflow-x: auto;">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr style="background-color: var(--primary-color); color: white;">
                                    <th>ID</th>
                                    <th>Data/Hora</th>
                                    <th>Utilizador</th>
                                    <th>Ação</th>
                                    <th>Módulo</th>
                                    <th>Descrição</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1001</td>
                                    <td>21/11/2025 14:30:45</td>
                                    <td><span class="rbac-badge rbac-admin">Admin</span> João Silva</td>
                                    <td><i class="fas fa-plus-circle log-create"></i> CREATE</td>
                                    <td>Utilizadores</td>
                                    <td>Novo utilizador criado: maria@veigest.com</td>
                                    <td><button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button></td>
                                </tr>
                                <tr>
                                    <td>1002</td>
                                    <td>21/11/2025 13:15:22</td>
                                    <td><span class="rbac-badge rbac-gestor">Gestor</span> Pedro Costa</td>
                                    <td><i class="fas fa-edit log-update"></i> UPDATE</td>
                                    <td>Veículos</td>
                                    <td>Veículo ABC-1234 atualizado - Próxima manutenção: 25/11/2025</td>
                                    <td><button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button></td>
                                </tr>
                                <tr>
                                    <td>1003</td>
                                    <td>21/11/2025 12:00:10</td>
                                    <td><span class="rbac-badge rbac-condutor">Condutor</span> Carlos Lima</td>
                                    <td><i class="fas fa-eye log-view"></i> VIEW</td>
                                    <td>Documentos</td>
                                    <td>Documentos pessoais consultados</td>
                                    <td><button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button></td>
                                </tr>
                                <tr>
                                    <td>1004</td>
                                    <td>20/11/2025 16:45:33</td>
                                    <td><span class="rbac-badge rbac-admin">Admin</span> João Silva</td>
                                    <td><i class="fas fa-trash-alt log-delete"></i> DELETE</td>
                                    <td>Veículos</td>
                                    <td>Veículo XYZ-7890 removido do sistema</td>
                                    <td><button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button></td>
                                </tr>
                                <tr>
                                    <td>1005</td>
                                    <td>20/11/2025 11:20:55</td>
                                    <td><span class="rbac-badge rbac-admin">Admin</span> João Silva</td>
                                    <td><i class="fas fa-edit log-update"></i> UPDATE</td>
                                    <td>Configurações</td>
                                    <td>Configurações do sistema alteradas - Email SMTP</td>
                                    <td><button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
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
