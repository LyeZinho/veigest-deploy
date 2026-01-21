<?php

/** @var yii\web\View $this */

$this->title = 'Gestão de Utilizadores';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Utilizadores - VeiGest</title>
    
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
        .btn-primary:hover { background-color: #088570 !important; }
        
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
                    <a href="profile.html" class="dropdown-item"><i class="fas fa-user mr-2"></i> Perfil</a>
                    <div class="dropdown-divider"></div>
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
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="https://via.placeholder.com/160x160?text=Admin" class="img-circle elevation-2" alt="Admin">
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
                    <li class="nav-header" style="color: #999;">ADMINISTRAÇÃO</li>
                    <li class="nav-item">
                        <a href="users.html" class="nav-link active">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Gestão de Utilizadores</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="system-settings.html" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Configurações</p>
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
    
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Gestão de Utilizadores</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus mr-2"></i>Novo Utilizador
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <section class="content">
            <div class="container-fluid">
                <!-- Filter Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Procurar nome ou email...">
                            </div>
                            <div class="col-md-4">
                                <select class="form-control">
                                    <option>Todos os Papéis</option>
                                    <option>Admin</option>
                                    <option>Gestor</option>
                                    <option>Condutor</option>
                                    <option>Convidado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-control">
                                    <option>Todos os Status</option>
                                    <option>Ativo</option>
                                    <option>Inativo</option>
                                    <option>Bloqueado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Utilizadores do Sistema</h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">87 Utilizadores</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Papel</th>
                                    <th>Status</th>
                                    <th>Último Acesso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>João Silva</td>
                                    <td>joao@veigest.pt</td>
                                    <td><span class="rbac-badge rbac-admin">Admin</span></td>
                                    <td><span class="badge badge-success">Ativo</span></td>
                                    <td>Hoje 14:30</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Maria Santos</td>
                                    <td>maria@veigest.pt</td>
                                    <td><span class="rbac-badge rbac-gestor">Gestor</span></td>
                                    <td><span class="badge badge-success">Ativo</span></td>
                                    <td>Ontem 09:15</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Carlos Mendes</td>
                                    <td>carlos@veigest.pt</td>
                                    <td><span class="rbac-badge rbac-condutor">Condutor</span></td>
                                    <td><span class="badge badge-warning">Inativo</span></td>
                                    <td>5 dias atrás</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Ana Costa</td>
                                    <td>ana@veigest.pt</td>
                                    <td><span class="rbac-badge rbac-convidado">Convidado</span></td>
                                    <td><span class="badge badge-danger">Bloqueado</span></td>
                                    <td>Nunca</td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    <!-- Modal Adicionar Utilizador -->
    <div class="modal fade" id="addUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--primary-color); color: white;">
                    <h4 class="modal-title">Novo Utilizador</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome Completo</label>
                            <input type="text" class="form-control" placeholder="Nome do utilizador">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" placeholder="email@veigest.pt">
                        </div>
                        <div class="form-group">
                            <label>Papel (Role)</label>
                            <select class="form-control">
                                <option>Selecione um papel...</option>
                                <option>Admin</option>
                                <option>Gestor</option>
                                <option>Condutor</option>
                                <option>Convidado</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Palavra-passe</label>
                            <input type="password" class="form-control" placeholder="Palavra-passe">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="main-footer">
        <strong>VeiGest &copy; 2025</strong>
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
</body>
</html>
