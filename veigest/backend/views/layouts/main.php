<?php

/** @var \yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $this->title ?: 'VeiGest';

$identity = Yii::$app->user->identity ?? null;
//nome do utilizador
$displayName = $identity
    ? (!empty($identity->name) ? $identity->name : (!empty($identity->username) ? $identity->username : 'Utilizador'))
    : 'Convidado';

$avatar = Url::to('@web/img/user-placeholder.png');

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= Html::encode($this->title) ?></title>

    <?= Html::csrfMetaTags() ?>
    <?php $this->head() ?>

    <!-- CSS do tema -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #09BC8A;
            --dark-color: #3C3C3C;
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        .nav-link:hover {
            background-color: rgba(9, 188, 138, 0.1) !important;
            color: var(--primary-color) !important;
        }

        .main-sidebar {
            background-color: #f8f9fa !important;
        }

        .brand-link {
            background-color: var(--primary-color) !important;
        }

        .img-circle {
            object-fit: cover;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <?php $this->beginBody() ?>

    <div class="wrapper">

        <!-- NAVBAR -->
        <nav class="main-header navbar navbar-expand navbar-dark" style="background-color: var(--dark-color);">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <?= Html::a('Início', ['/site/index'], ['class' => 'nav-link']) ?>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="<?= Yii::getAlias('@frontendUrl') ?>"
                        class="nav-link"
                        target="_blank">
                        Frontend
                    </a>
                </li>


            </ul>

            <ul class="navbar-nav ml-auto">

                <!-- Notificações -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-bell"></i>
                        <span class="badge badge-danger navbar-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <span class="dropdown-header">3 Notificações</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-warning text-warning mr-2"></i> Manutenção programada
                        </a>
                    </div>
                </li>

                <!-- Perfil -->
                <li class="nav-item dropdown">
                    <?php if (Yii::$app->user->isGuest): ?>
                        <a href="<?= Url::to(['/site/login']) ?>" class="nav-link">
                            <i class="fas fa-sign-in-alt"></i>
                        </a>
                    <?php else: ?>
                        <a class="nav-link" data-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="fas fa-user mr-2"></i> Perfil', ['/user/view', 'id' => $identity->id], ['class' => 'dropdown-item']) ?>
                            <div class="dropdown-divider"></div>
                            <?= Html::beginForm(['/site/logout'], 'post')
                                . Html::submitButton('<i class="fas fa-sign-out-alt mr-2"></i> Sair (' . Html::encode($identity->username) . ')', ['class' => 'dropdown-item'])
                                . Html::endForm() ?>
                        </div>
                    <?php endif; ?>
                </li>

            </ul>
        </nav>


        <!-- SIDEBAR -->
        <aside class="main-sidebar elevation-4">

            <!-- Logo -->
            <a href="<?= Url::to(['/site/index']) ?>" class="brand-link d-flex align-items-center">
                <img src="<?= Yii::getAlias('@web') ?>/images/veigest-logo.png"
                    style="width:35px;height:35px;margin-right:10px;">
                <span class="brand-text font-weight-bold" style="color:white;">VeiGest</span>
            </a>

            <div class="sidebar">

                <!-- Painel do utilizador -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="<?= $avatar ?>" class="img-circle elevation-2" style="width:40px;height:40px;">
                    </div>
                    <div class="info">
                        <?php if (Yii::$app->user->isGuest): ?>
                            <a href="<?= Url::to(['/site/login']) ?>" class="d-block" style="font-weight:600;color:var(--dark-color);">
                                Convidado
                            </a>
                        <?php else: ?>
                            <a class="d-block" href="<?= Url::to(['/user/view', 'id' => $identity->id]) ?>" style="font-weight:600;color:var(--dark-color);">
                                <?= Html::encode($displayName) ?>
                            </a>

                            <?php if (Yii::$app->user->can('admin')): ?>
                                <span class="badge badge-danger mt-1">Admin</span>
                            <?php elseif (!empty($identity->role)): ?>
                                <span class="badge badge-secondary mt-1"><?= Html::encode($identity->role) ?></span>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>

                <!-- MENU -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" role="menu">

                        <li class="nav-item">
                            <?= Html::a(
                                '<i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>',
                                ['/site/index'],
                                ['class' => 'nav-link ' . (Yii::$app->controller->id == 'site' ? 'active' : '')]
                            ) ?>
                        </li>

                        <?php if (Yii::$app->user->can('admin')): ?>

                            <li class="nav-header" style="color:#999;">ADMINISTRAÇÃO</li>

                            <li class="nav-item">
                                <?= Html::a('<i class="nav-icon fas fa-building"></i><p>Gestão de Empresas</p>', ['/company/index'], ['class' => 'nav-link']) ?>
                            </li>

                            <li class="nav-item">
                                <?= Html::a('<i class="nav-icon fas fa-users"></i><p>Gestão de Utilizadores</p>', ['/user/index'], ['class' => 'nav-link']) ?>
                            </li>

                            <li class="nav-item">
                                <?= Html::a('<i class="nav-icon fas fa-cogs"></i><p>Configurações</p>', ['/system/settings'], ['class' => 'nav-link']) ?>
                            </li>

                        <?php endif; ?>

                        <?php if (Yii::$app->user->can('admin') || Yii::$app->user->can('manager')): ?>
                            <li class="nav-header" style="color:#999;">SUPORTE</li>

                            <li class="nav-item">
                                <?= Html::a(
                                    '<i class="nav-icon fas fa-ticket-alt"></i><p>Tickets de Suporte</p>',
                                    ['/ticket/index'],
                                    ['class' => 'nav-link ' . (Yii::$app->controller->id == 'ticket' ? 'active' : '')]
                                ) ?>
                            </li>
                        <?php endif; ?>


                    </ul>
                </nav>
            </div>
        </aside>


        <!-- CONTENT -->
        <div class="content-wrapper">
            <section class="content pt-3">
                <div class="container-fluid">
                    <?= $content ?>
                </div>
            </section>
        </div>

        <!-- FOOTER -->
        <footer class="main-footer">
            <strong>VeiGest &copy; <?= date('Y') ?></strong>
            <div class="float-right d-none d-sm-inline-block">Versão 1.0.0</div>
        </footer>

    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>