<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Gestão de Frota';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-11 col-xxl-10 d-flex align-items-center justify-content-between flex-wrap">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11 col-xxl-10">
            <div class="card w-100" style="max-width: 1200px; margin: 0 auto;">
                <div class="card-header">
                    <?php if (Yii::$app->user->can('vehicles.create')): ?>
                        <div class="card-tools">
                            <?= Html::a('<i class="fas fa-plus mr-1"></i>Novo Veículo', ['vehicle/create'], ['class' => 'btn btn-primary btn-sm']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-body p-0">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => '<div class="pl-3 pt-2">Mostrando <strong>{begin}-{end}</strong> de <strong>{totalCount}</strong> itens.</div>',
                        'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                        'headerRowOptions' => ['style' => 'background:#f7f9fb;'],
                        'columns' => [
                            [
                                'attribute' => 'license_plate',
                                'label' => 'Matrícula',
                                'contentOptions' => ['style' => 'min-width:140px; font-weight:600;']
                            ],
                            [
                                'label' => 'Marca / Modelo',
                                'value' => function($v){ return trim(($v->brand ?? '') . ' ' . ($v->model ?? '')); },
                                'contentOptions' => ['style' => 'min-width:200px;']
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Estado',
                                'value' => function($v){
                                    $map = [
                                        'ativo' => ['Ativo', 'badge-success'],
                                        'inativo' => ['Inativo', 'badge-secondary'],
                                        'manutencao' => ['Manutenção', 'badge-warning'],
                                    ];
                                    [$text, $badge] = $map[$v->status] ?? [ucfirst($v->status), 'badge-secondary'];
                                    return '<span class="badge ' . $badge . '">' . $text . '</span>';
                                },
                                'format' => 'html',
                                'contentOptions' => ['style' => 'width:130px;']
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'controller' => 'vehicle',
                                'header' => 'Ações',
                                'headerOptions' => ['style' => 'width:200px; text-align:center;'],
                                'contentOptions' => ['style' => 'text-align:center; white-space:nowrap;'],
                                'template' => '{view} {update} {delete}',
                                'buttons' => [
                                    'view' => function($url, $v){
                                        return Yii::$app->user->can('vehicles.view')
                                            ? Html::a('Ver', $url, ['class' => 'btn btn-outline-info btn-sm me-1'])
                                            : '';
                                    },
                                    'update' => function($url, $v){
                                        return Yii::$app->user->can('vehicles.update')
                                            ? Html::a('Editar', $url, ['class' => 'btn btn-outline-warning btn-sm me-1'])
                                            : '';
                                    },
                                    'delete' => function($url, $v){
                                        return Yii::$app->user->can('vehicles.delete')
                                            ? Html::a('Apagar', $url, [
                                                'class' => 'btn btn-outline-danger btn-sm',
                                                'data' => [
                                                    'confirm' => 'Remover este veículo?',
                                                    'method' => 'post',
                                                ],
                                            ])
                                            : '';
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

