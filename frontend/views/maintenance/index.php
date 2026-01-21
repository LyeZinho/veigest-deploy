<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Manutenções';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-11 col-xxl-10 d-flex align-items-center justify-content-between flex-wrap">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
            <?php if (Yii::$app->user->can('maintenances.create')): ?>
                <div>
                    <?= Html::a('<i class="fas fa-plus mr-2"></i>Agendar Manutenção', ['create'], ['class' => 'btn btn-primary']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11 col-xxl-10">
            <!-- Tabs for filtering -->
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <?= Html::a('Agendadas', ['index', 'status' => 'scheduled'], [
                        'class' => 'nav-link' . ($status === 'scheduled' ? ' active' : ''),
                        'role' => 'tab',
                    ]) ?>
                </li>
                <li class="nav-item">
                    <?= Html::a('Concluídas', ['index', 'status' => 'completed'], [
                        'class' => 'nav-link' . ($status === 'completed' ? ' active' : ''),
                        'role' => 'tab',
                    ]) ?>
                </li>
            </ul>

            <div class="card">
                <div class="card-body p-0">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            'id',
                            [
                                'attribute' => 'vehicle_id',
                                'label' => 'Veículo',
                                'value' => function($model) {
                                    return $model->vehicle ? $model->vehicle->model . ' (' . $model->vehicle->license_plate . ')' : '-';
                                },
                            ],
                            [
                                'attribute' => 'type',
                                'label' => 'Tipo',
                            ],
                            [
                                'attribute' => 'date',
                                'label' => 'Data',
                                'format' => ['date', 'php:d/m/Y'],
                            ],
                            [
                                'attribute' => 'cost',
                                'label' => 'Custo',
                                'value' => function($model) {
                                    return $model->cost !== null ? number_format($model->cost, 2, ',', '.') . ' €' : '-';
                                },
                            ],
                            [
                                'attribute' => 'workshop',
                                'label' => 'Oficina',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Ações',
                                'headerOptions' => ['style' => 'width:180px; text-align:center'],
                                'contentOptions' => ['style' => 'text-align:center; white-space:nowrap'],
                                'template' => $status === 'scheduled' ? '{view} {complete} {update} {delete}' : '{view}',
                                'buttons' => [
                                    'view' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.view')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-info', 'title' => 'Ver']);
                                    },
                                    'complete' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.update')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-check"></i>', ['complete', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-success',
                                            'title' => 'Concluir',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que pretende marcar esta manutenção como concluída?',
                                                'method' => 'post',
                                            ],
                                        ]);
                                    },
                                    'update' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.update')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-warning', 'title' => 'Editar']);
                                    },
                                    'delete' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.delete')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'Eliminar',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que pretende eliminar esta manutenção?',
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
    </div>
</div>
