<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\models\Driver;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */

$this->title = 'Condutores';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-users mr-2"></i><?= Html::encode($this->title) ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active"><?= Html::encode($this->title) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Estatísticas -->
            <?php if (isset($stats)): ?>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $stats['total'] ?? 0 ?></h3>
                            <p>Total Condutores</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $stats['active'] ?? 0 ?></h3>
                            <p>Ativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= $stats['inactive'] ?? 0 ?></h3>
                            <p>Inativos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-slash"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $stats['expiring_license'] ?? 0 ?></h3>
                            <p>Carta a Expirar</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista de Condutores -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Lista de Condutores</h3>
                    <div class="card-tools">
                        <?php if (Yii::$app->user->can('drivers.create')): ?>
                            <?= Html::a('<i class="fas fa-plus"></i> Novo Condutor', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'name',
                                'label' => 'Nome',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $avatarUrl = $model->getAvatarUrl();
                                    $avatar = $avatarUrl 
                                        ? '<img src="' . $avatarUrl . '" class="img-circle elevation-1 mr-2" style="width: 30px; height: 30px; object-fit: cover; vertical-align: middle; display: inline-block;">'
                                        : '<div class="img-circle elevation-1 bg-primary d-inline-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; vertical-align: middle; display: inline-flex;"><i class="fas fa-user text-white" style="font-size: 12px;"></i></div>';
                                    return '<div style="display: flex; align-items: center;">' . $avatar . '<strong>' . Html::encode($model->getDisplayName()) . '</strong></div>';
                                },
                            ],
                            [
                                'attribute' => 'email',
                                'format' => 'email',
                            ],
                            [
                                'attribute' => 'phone',
                                'label' => 'Telefone',
                                'value' => function($model) {
                                    return $model->phone ?: '-';
                                },
                            ],
                            [
                                'attribute' => 'license_number',
                                'label' => 'Carta',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if (empty($model->license_number)) {
                                        return '<span class="text-muted">-</span>';
                                    }
                                    $licenseValid = $model->isLicenseValid();
                                    $days = $model->getDaysUntilLicenseExpiry();
                                    
                                    $badge = '';
                                    if ($licenseValid === false) {
                                        $badge = ' <span class="badge badge-danger">Expirada</span>';
                                    } elseif ($days !== null && $days <= 30) {
                                        $badge = ' <span class="badge badge-warning">Expira em ' . $days . ' dias</span>';
                                    }
                                    
                                    return Html::encode($model->license_number) . $badge;
                                },
                            ],
                            [
                                'label' => 'Veículos',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $count = $model->getVehicleCount();
                                    return '<span class="badge badge-info">' . $count . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $isActive = $model->status === Driver::STATUS_ACTIVE;
                                    if ($isActive) {
                                        return '<span class="badge badge-success"><i class="fas fa-check"></i> Ativo</span>';
                                    }
                                    return '<span class="badge badge-secondary"><i class="fas fa-pause"></i> Inativo</span>';
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Ações',
                                'template' => '{view} {update} {delete}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        if (!Yii::$app->user->can('drivers.view')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'class' => 'btn btn-info btn-sm mr-1',
                                            'title' => 'Ver detalhes',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'update' => function ($url, $model, $key) {
                                        if (!Yii::$app->user->can('drivers.update')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning btn-sm mr-1',
                                            'title' => 'Editar',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        if (!Yii::$app->user->can('drivers.delete')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger btn-sm',
                                            'title' => 'Apagar',
                                            'data-toggle' => 'tooltip',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja apagar este condutor?',
                                                'method' => 'post',
                                            ],
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
$this->registerJs(<<<JS
    $('[data-toggle="tooltip"]').tooltip();
JS
);
?>
