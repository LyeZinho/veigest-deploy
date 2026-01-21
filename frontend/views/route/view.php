<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var frontend\models\Route $model */

$this->title = 'Rota #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Rotas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-route mr-2"></i><?= Html::encode($this->title) ?>
                        <?php
                        // Determinar estado
                        if ($model->end_time):
                        ?>
                            <span class="badge badge-success ml-2">Concluída</span>
                        <?php elseif (strtotime($model->start_time) <= time()): ?>
                            <span class="badge badge-info ml-2">Em Curso</span>
                        <?php else: ?>
                            <span class="badge badge-warning ml-2">Pendente</span>
                        <?php endif; ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Rotas</a></li>
                        <li class="breadcrumb-item active"><?= Html::encode($this->title) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Botões de Ação -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
                        <?php if (Yii::$app->user->can('routes.update')): ?>
                            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('routes.delete')): ?>
                            <?= Html::a('<i class="fas fa-trash"></i> Apagar', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Tem a certeza que deseja apagar esta rota?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Detalhes da Rota -->
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Detalhes da Rota</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                'attributes' => [
                                    [
                                        'attribute' => 'id',
                                        'label' => 'ID',
                                    ],
                                    [
                                        'attribute' => 'start_time',
                                        'label' => 'Data/Hora Início',
                                        'value' => Yii::$app->formatter->asDatetime($model->start_time, 'php:d/m/Y H:i'),
                                    ],
                                    [
                                        'attribute' => 'end_time',
                                        'label' => 'Data/Hora Fim',
                                        'value' => $model->end_time ? Yii::$app->formatter->asDatetime($model->end_time, 'php:d/m/Y H:i') : '-',
                                    ],
                                    [
                                        'attribute' => 'start_location',
                                        'label' => 'Origem',
                                        'format' => 'raw',
                                        'value' => '<i class="fas fa-map-marker-alt text-danger mr-2"></i>' . Html::encode($model->start_location),
                                    ],
                                    [
                                        'attribute' => 'end_location',
                                        'label' => 'Destino',
                                        'format' => 'raw',
                                        'value' => '<i class="fas fa-flag-checkered text-success mr-2"></i>' . Html::encode($model->end_location),
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'label' => 'Criado em',
                                        'value' => Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i'),
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Condutor e Veículo -->
                <div class="col-md-6">
                    <!-- Condutor -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i>Condutor</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($model->driver): ?>
                                <div class="d-flex align-items-center">
                                    <div class="img-circle elevation-2 bg-primary d-flex align-items-center justify-content-center mr-3" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-user fa-2x text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?= Html::encode($model->driver->name ?? $model->driver->username) ?></h5>
                                        <p class="text-muted mb-0">
                                            <i class="fas fa-envelope mr-1"></i><?= Html::encode($model->driver->email) ?>
                                        </p>
                                        <?php if (Yii::$app->user->can('drivers.view')): ?>
                                            <?= Html::a('<i class="fas fa-external-link-alt"></i> Ver Perfil', ['driver/view', 'id' => $model->driver->id], ['class' => 'btn btn-link btn-sm p-0']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Nenhum condutor atribuído</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Veículo -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-car mr-2"></i>Veículo</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($model->vehicle): ?>
                                <div class="d-flex align-items-center">
                                    <div class="img-circle elevation-2 bg-success d-flex align-items-center justify-content-center mr-3" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-car fa-2x text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?= Html::encode($model->vehicle->license_plate) ?></h5>
                                        <p class="text-muted mb-1">
                                            <?= Html::encode($model->vehicle->brand . ' ' . $model->vehicle->model . ' (' . $model->vehicle->year . ')') ?>
                                        </p>
                                        <?php
                                        $vehicleStatuses = [
                                            'ativo' => '<span class="badge badge-success">Ativo</span>',
                                            'inativo' => '<span class="badge badge-secondary">Inativo</span>',
                                            'manutencao' => '<span class="badge badge-warning">Manutenção</span>',
                                        ];
                                        echo $vehicleStatuses[$model->vehicle->status] ?? '';
                                        ?>
                                        <?php if (Yii::$app->user->can('vehicles.view')): ?>
                                            <br>
                                            <?= Html::a('<i class="fas fa-external-link-alt"></i> Ver Veículo', ['vehicle/view', 'id' => $model->vehicle->id], ['class' => 'btn btn-link btn-sm p-0']) ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-car-side fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Nenhum veículo atribuído</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
