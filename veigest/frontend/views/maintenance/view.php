<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\Maintenance;

/** @var yii\web\View $this */
/** @var frontend\models\Maintenance $model */

$this->title = $model->vehicle->model . ' - ' . $model->tipo;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Manutenções', 'url' => ['maintenance/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-11 col-xxl-10 d-flex align-items-center justify-content-between flex-wrap">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
            <div>
                <?php if (Yii::$app->user->can('maintenances.update')): ?>
                    <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?php endif; ?>
                <?php if (Yii::$app->user->can('maintenances.delete')): ?>
                    <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => 'Tem a certeza que pretende eliminar esta manutenção?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11 col-xxl-10">
            <div class="card">
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'vehicle_id',
                                'value' => function($model) {
                                    return $model->vehicle ? $model->vehicle->model . ' (' . $model->vehicle->license_plate . ')' : '-';
                                },
                                'label' => 'Veículo',
                            ],
                            'tipo:text:Tipo de Manutenção',
                            'data:date:Data',
                            [
                                'attribute' => 'status',
                                'value' => function($model) {
                                    $labels = [
                                        'scheduled' => '<span class="badge bg-info">Agendada</span>',
                                        'completed' => '<span class="badge bg-success">Concluída</span>',
                                        'overdue' => '<span class="badge bg-danger">Atrasada</span>',
                                    ];
                                    return $labels[$model->status] ?? $model->status;
                                },
                                'format' => 'raw',
                                'label' => 'Estado',
                            ],
                            [
                                'attribute' => 'custo',
                                'value' => function($model) {
                                    return $model->custo !== null ? number_format($model->custo, 2, ',', '.') . ' €' : '-';
                                },
                                'label' => 'Custo',
                            ],
                            'km_registro:integer:Quilometragem (km)',
                            'oficina:text:Oficina',
                            'descricao:ntext:Descrição',
                            [
                                'attribute' => 'created_at',
                                'format' => ['date', 'php:d/m/Y H:i'],
                                'label' => 'Criado em',
                            ],
                            [
                                'attribute' => 'updated_at',
                                'format' => ['date', 'php:d/m/Y H:i'],
                                'label' => 'Atualizado em',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
