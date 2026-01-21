<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\DashboardAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

DashboardAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - VeiGest</title>
    <?php $this->head() ?>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .small-box {
            border-radius: 8px;
        }

        .small-box.bg-teal {
            background-color: var(--primary-color) !important;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Ocultar texto do logo quando sidebar estiver colapsada */
        .sidebar-collapse .brand-link span {
            display: none;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">


    <?php $this->beginBody() ?>
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
                    <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/index']) ?>" class="nav-link">Início</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" id="notificationToggle">
                        <i class="fas fa-bell"></i>
                        <span class="badge badge-danger navbar-badge" id="notificationBadge" style="display: none;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificationMenu">
                        <span class="dropdown-header">Carregando...</span>
                    </div>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="<?= Yii::$app->urlManager->createUrl(['profile/index']) ?>" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Meu Perfil
                        </a>
                        <a href="<?= Yii::$app->urlManager->createUrl(['profile/change-password']) ?>" class="dropdown-item">
                            <i class="fas fa-key mr-2"></i> Alterar Palavra-passe
                        </a>
                        <div class="dropdown-divider"></div>
                        <?= Html::beginForm(['/site/logout'], 'post', ['id' => 'logout-form', 'style' => 'display: inline;']) ?>
                            <?= Html::submitButton(
                                '<i class="fas fa-sign-out-alt mr-2"></i> Sair',
                                ['class' => 'dropdown-item', 'style' => 'border: none; background: none; width: 100%; text-align: left; cursor: pointer;']
                            ) ?>
                        <?= Html::endForm() ?>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #f8f9fa;">

            <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/index']) ?>" class="brand-link d-flex align-items-center">
                <img src="<?= Yii::getAlias('@web') ?>/images/veigest-logo.png" style="width: 35px; height: 35px; margin-right: 10px;">
                <span style="color: white; font-weight: 700;">VeiGest</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="info">
                        <a href="#" class="d-block" style="color: var(--dark-color); font-weight: 600;"><?= Yii::$app->user->identity->name ?? 'User' ?></a>
                        <?php
                        $role = Yii::$app->user->identity->role;
                        ?>

                        <?php if ($role === 'admin'): ?>
                            <span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.75rem;font-weight:600;background-color:#dc3545;color:white;">
                                Administrador
                            </span>

                        <?php elseif ($role === 'manager'): ?>
                            <span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.75rem;font-weight:600;background-color:#09BC8A;color:white;">
                                Gestor
                            </span>

                        <?php elseif ($role === 'driver'): ?>
                            <span style="display:inline-block;padding:4px 8px;border-radius:4px;font-size:0.75rem;font-weight:600;background-color:#6c757d;color:white;">
                                Condutor
                            </span>
                        <?php endif; ?>

                    </div>
                </div>

                <?php 
                // Get user role for menu visibility control
                $userRole = Yii::$app->user->identity->role ?? null;
                $isManager = ($userRole === 'manager');
                $isDriver = ($userRole === 'driver');
                $isAdmin = ($userRole === 'admin');
                ?>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        
                        <!-- Dashboard - All roles -->
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/index']) ?>" class="nav-link active">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Fleet Section -->
                        <li class="nav-item menu-open">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-car"></i>
                                <p>Frota <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <!-- Vehicles - All roles (view only for driver) -->
                                <li class="nav-item">
                                    <a href="<?= Yii::$app->urlManager->createUrl(['vehicle/index']) ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Veículos</p>
                                    </a>
                                </li>
                                
                                <?php if ($isManager): ?>
                                <!-- Drivers - Manager only -->
                                <li class="nav-item">
                                    <a href="<?= Yii::$app->urlManager->createUrl(['driver/index']) ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Condutores</p>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <!-- Routes - All roles (view only for driver) -->
                                <li class="nav-item">
                                    <a href="<?= Yii::$app->urlManager->createUrl(['route/index']) ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rotas</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php if ($isManager): ?>
                        <!-- Maintenance - Manager only -->
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['maintenance/index']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-tools"></i>
                                <p>Manutenção</p>
                            </a>
                        </li>

                        <!-- Documents - Manager only -->
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['document/index']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Documentos</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Alerts - All roles (view only for driver) -->
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['alert/index']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-bell"></i>
                                <p>Alertas</p>
                            </a>
                        </li>

                        <?php if ($isManager): ?>
                        <!-- Reports - Manager only -->
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['report/index']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Relatórios</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <li class="nav-header">SUPORTE</li>
                        <!-- Support Tickets - All roles -->
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['site/my-tickets']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-ticket-alt"></i>
                                <p>Meus Tickets</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['site/ticket']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Novo Ticket</p>
                            </a>
                        </li>

                        <li class="nav-header">CONTA</li>
                        <!-- Profile - All roles (edit allowed for all) -->
                        <li class="nav-item">
                            <a href="<?= Yii::$app->urlManager->createUrl(['profile/index']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-user-circle"></i>
                                <p>Meu Perfil</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->

        <!-- 
     Aqui deve ser inserido o conteúdo específico de cada página
    -->
        <div class="content-wrapper">
            <section class="content pt-3">
                <div class="container-fluid">
                    <?= $content ?>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong>VeiGest &copy; 2025</strong>
            <div class="float-right d-none d-sm-inline-block">
                <b>Versão</b> 1.0.0
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

    <script>
        // Carregar notificações ao iniciar
        $(function() {
            loadNotifications();
            // Recarregar a cada 30 segundos
            setInterval(loadNotifications, 30000);
        });

        function loadNotifications() {
            $.ajax({
                url: '<?= \yii\helpers\Url::to(['alert/notifications']) ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    updateNotificationUI(response);
                },
                error: function() {
                    console.log('Erro ao carregar notificações');
                }
            });
        }

        function updateNotificationUI(response) {
            const count = response.count;
            const items = response.items;
            const badge = $('#notificationBadge');
            const menu = $('#notificationMenu');

            // Atualizar badge
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }

            // Construir menu
            let html = '<span class="dropdown-header">' + count + ' Notificação' + (count !== 1 ? 's' : '') + '</span>';
            html += '<div class="dropdown-divider"></div>';

            if (count === 0) {
                html += '<a href="#" class="dropdown-item text-muted">Nenhuma notificação no momento</a>';
            } else {
                items.forEach(function(item) {
                    let icon = 'fa-bell text-info';
                    
                    // Críticos = vermelho
                    if (item.type === 'doc_expired' || item.type === 'maint_late') {
                        icon = 'fa-exclamation-circle text-danger';
                    } 
                    // Próximos = amarelo/laranja
                    else if (item.type === 'doc_near' || item.type === 'maint_near') {
                        icon = 'fa-clock text-warning';
                    }
                    
                    html += '<a href="' + item.url + '" class="dropdown-item">';
                    html += '<i class="fas ' + icon + ' mr-2"></i> ';
                    html += '<strong>' + item.title + '</strong><br>';
                    html += '<small class="text-muted">' + item.message + '</small>';
                    html += '</a>';
                    html += '<div class="dropdown-divider"></div>';
                });

                html += '<a href="<?= \yii\helpers\Url::to(['alert/index']) ?>" class="dropdown-item dropdown-footer">';
                html += '<i class="fas fa-eye mr-2"></i> Ver todos os alertas';
                html += '</a>';
            }

            menu.html(html);
        }
    </script>

    <?php $this->endBody() ?>
    
    <!-- Yii Framework JS for POST method handling - MUST be last after all other scripts -->
    <script src="<?= \yii\helpers\Url::to('@web/assets/9140a366/yii.js') ?>"></script>
</body>

</html>
<?php $this->endPage() ?>