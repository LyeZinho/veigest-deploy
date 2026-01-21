<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Veículos';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-car mr-2"></i><?= Html::encode($this->title) ?>
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
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Lista de Veículos</h3>
                    <div class="card-tools">
                        <?php if (Yii::$app->user->can('vehicles.create')): ?>
                            <?= Html::a('<i class="fas fa-plus"></i> Novo Veículo', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
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
                                'attribute' => 'license_plate',
                                'label' => 'Matrícula',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '<strong>' . Html::encode($model->license_plate) . '</strong>';
                                },
                            ],
                            [
                                'attribute' => 'brand',
                                'value' => function($model) {
                                    return Html::encode($model->brand . ' ' . $model->model);
                                },
                                'label' => 'Marca/Modelo',
                            ],
                            [
                                'attribute' => 'year',
                                'label' => 'Ano',
                            ],
                            [
                                'attribute' => 'fuel_type',
                                'label' => 'Combustível',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $fuelTypes = [
                                        'gasolina' => '<span class="badge badge-danger"><i class="fas fa-gas-pump"></i> Gasolina</span>',
                                        'diesel' => '<span class="badge badge-warning"><i class="fas fa-gas-pump"></i> Diesel</span>',
                                        'eletrico' => '<span class="badge badge-success"><i class="fas fa-bolt"></i> Elétrico</span>',
                                        'hibrido' => '<span class="badge badge-info"><i class="fas fa-leaf"></i> Híbrido</span>',
                                        'gpl' => '<span class="badge badge-primary"><i class="fas fa-fire"></i> GPL</span>',
                                    ];
                                    return $fuelTypes[$model->fuel_type] ?? Html::encode($model->fuel_type);
                                },
                            ],
                            [
                                'attribute' => 'mileage',
                                'label' => 'Km',
                                'value' => function($model) {
                                    return number_format($model->mileage, 0, ',', '.') . ' km';
                                },
                            ],
                            [
                                'attribute' => 'driver_id',
                                'label' => 'Condutor',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->driver) {
                                        return '<i class="fas fa-user text-primary"></i> ' . Html::encode($model->driver->name ?? $model->driver->username);
                                    }
                                    return '<span class="text-muted"><i class="fas fa-user-slash"></i> Não atribuído</span>';
                                },
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => function($model) {
                                    $statuses = [
                                        'ativo' => '<span class="badge badge-success"><i class="fas fa-check"></i> Ativo</span>',
                                        'inativo' => '<span class="badge badge-secondary"><i class="fas fa-pause"></i> Inativo</span>',
                                        'manutencao' => '<span class="badge badge-warning"><i class="fas fa-wrench"></i> Manutenção</span>',
                                    ];
                                    return $statuses[$model->status] ?? Html::encode($model->status);
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} {history} {update} {delete}',
                                'buttons' => [
                                    'view' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('vehicles.view')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'class' => 'btn btn-info btn-sm mr-1',
                                            'title' => 'Ver detalhes',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'history' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('vehicles.view')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-history"></i>', ['history', 'id' => $model->id], [
                                            'class' => 'btn btn-primary btn-sm mr-1',
                                            'title' => 'Ver histórico',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'update' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('vehicles.update')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning btn-sm mr-1',
                                            'title' => 'Editar',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'delete' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('vehicles.delete')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger btn-sm',
                                            'title' => 'Apagar',
                                            'data-toggle' => 'tooltip',
                                            'data' => [
                                                'confirm' => 'Tem certeza que deseja apagar este veículo?',
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
