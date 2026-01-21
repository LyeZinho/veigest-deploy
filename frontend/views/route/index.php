<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Rotas';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-route mr-2"></i><?= Html::encode($this->title) ?>
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
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Lista de Rotas</h3>
                    <div class="card-tools">
                        <?php if (Yii::$app->user->can('routes.create')): ?>
                            <?= Html::a('<i class="fas fa-plus"></i> Nova Rota', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'start_time',
                                'label' => 'Data/Hora',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '<i class="fas fa-calendar-alt text-primary mr-1"></i>' . 
                                           Yii::$app->formatter->asDatetime($model->start_time, 'php:d/m/Y H:i');
                                },
                            ],
                            [
                                'label' => 'Condutor',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->driver) {
                                        return '<i class="fas fa-user text-info mr-1"></i>' . Html::encode($model->driver->name ?? $model->driver->username);
                                    }
                                    return '<span class="text-muted">-</span>';
                                },
                            ],
                            [
                                'label' => 'Veículo',
                                'format' => 'raw',
                                'value' => function($model) {
                                    if ($model->vehicle) {
                                        return '<i class="fas fa-car text-success mr-1"></i>' . Html::encode($model->vehicle->license_plate);
                                    }
                                    return '<span class="text-muted">-</span>';
                                },
                            ],
                            [
                                'attribute' => 'start_location',
                                'label' => 'Origem',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '<i class="fas fa-map-marker-alt text-danger mr-1"></i>' . Html::encode($model->start_location);
                                },
                            ],
                            [
                                'attribute' => 'end_location',
                                'label' => 'Destino',
                                'format' => 'raw',
                                'value' => function($model) {
                                    return '<i class="fas fa-flag-checkered text-success mr-1"></i>' . Html::encode($model->end_location);
                                },
                            ],
                            [
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => function($model) {
                                    // Determinar estado baseado em end_time
                                    if ($model->end_time) {
                                        return '<span class="badge badge-success"><i class="fas fa-check"></i> Concluída</span>';
                                    }
                                    if (strtotime($model->start_time) <= time()) {
                                        return '<span class="badge badge-info"><i class="fas fa-truck-moving"></i> Em Curso</span>';
                                    }
                                    return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendente</span>';
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Ações',
                                'template' => '{view} {update} {delete}',
                                'buttons' => [
                                    'view' => function($url, $model) {
                                        if (!Yii::$app->user->can('routes.view')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'class' => 'btn btn-info btn-sm mr-1',
                                            'title' => 'Ver detalhes',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'update' => function($url, $model) {
                                        if (!Yii::$app->user->can('routes.update')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'class' => 'btn btn-warning btn-sm mr-1',
                                            'title' => 'Editar',
                                            'data-toggle' => 'tooltip',
                                        ]);
                                    },
                                    'delete' => function($url, $model) {
                                        if (!Yii::$app->user->can('routes.delete')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-danger btn-sm',
                                            'title' => 'Apagar',
                                            'data-toggle' => 'tooltip',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja apagar esta rota?',
                                                'method' => 'post',
                                            ],
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]) ?>
                    <?php Pjax::end(); ?>
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
