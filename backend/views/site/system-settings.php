<?php

/** @var yii\web\View $this */

$this->title = 'Configurações do Sistema';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema - VeiGest</title>
    
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
        
        .settings-section {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .settings-section:last-child {
            border-bottom: none;
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
                        <a href="system-settings.html" class="nav-link active">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Configurações</p>
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
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Configurações do Sistema</h1>
                    </div>
                </div>
            </div>
        </div>
        
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <!-- Configurações Gerais -->
                        <div class="settings-section">
                            <h4><i class="fas fa-sliders-h mr-2"></i>Configurações Gerais</h4>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nome da Empresa</label>
                                        <input type="text" class="form-control" value="VeiGest - Gestão de Frotas">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email Administrativo</label>
                                        <input type="email" class="form-control" value="admin@veigest.pt">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fuso Horário</label>
                                        <select class="form-control">
                                            <option selected>Europe/Lisbon</option>
                                            <option>Europe/Madrid</option>
                                            <option>Europe/London</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Idioma</label>
                                        <select class="form-control">
                                            <option selected>Português</option>
                                            <option>Inglês</option>
                                            <option>Espanhol</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segurança -->
                        <div class="settings-section">
                            <h4><i class="fas fa-shield-alt mr-2"></i>Segurança</h4>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="d-block">
                                            <input type="checkbox" checked> Autenticação de Dois Fatores (2FA)
                                        </label>
                                        <small class="text-muted">Exigir 2FA para todos os utilizadores Admin</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sessão Máxima (minutos)</label>
                                        <input type="number" class="form-control" value="60">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="d-block">
                                            <input type="checkbox"> Logs de Auditoria
                                        </label>
                                        <small class="text-muted">Registar todas as ações de Admin</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notificações -->
                        <div class="settings-section">
                            <h4><i class="fas fa-bell mr-2"></i>Notificações</h4>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="d-block">
                                            <input type="checkbox" checked> Alertas de Manutenção
                                        </label>
                                        <label class="d-block">
                                            <input type="checkbox" checked> Alertas de Documentos Expirados
                                        </label>
                                        <label class="d-block">
                                            <input type="checkbox"> Alertas de Consumo Anómalo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Backups -->
                        <div class="settings-section">
                            <h4><i class="fas fa-database mr-2"></i>Backups</h4>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Frequência de Backup</label>
                                        <select class="form-control">
                                            <option>Diário</option>
                                            <option selected>Semanal</option>
                                            <option>Mensal</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6 pt-4">
                                    <button class="btn btn-info btn-block"><i class="fas fa-download mr-2"></i>Fazer Backup Agora</button>
                                </div>
                            </div>
                            <small class="text-muted">Último backup: 20/11/2024 às 02:30</small>
                        </div>
                        
                        <!-- Ações -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button class="btn btn-primary" onclick="alert('Configurações guardadas!')"><i class="fas fa-save mr-2"></i>Guardar Alterações</button>
                                <button class="btn btn-secondary" onclick="window.history.back()"><i class="fas fa-times mr-2"></i>Cancelar</button>
                            </div>
                        </div>
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
</body>
</html>
