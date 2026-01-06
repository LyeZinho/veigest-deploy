<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\models\Maintenance;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $status */

$this->title = 'Plano de Manutenção';
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-11 col-xxl-10 d-flex align-items-center justify-content-between flex-wrap">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
            <?php if (Yii::$app->user->can('maintenances.create')): ?>
                <div>
                    <?= Html::a('<i class="fas fa-plus mr-2"></i>Agendar Manutenção', ['maintenance/create'], ['class' => 'btn btn-primary']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11 col-xxl-10">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6">
                    <?= Html::a(
                        '<div class="small-box" style="background-color: #17a2b8;">
                            <div class="inner text-white">
                                <h3>' . ($stats['scheduled'] ?? 0) . '</h3>
                                <p>Agendadas</p>
                            </div>
                            <div class="icon"><i class="fas fa-calendar"></i></div>
                        </div>',
                        ['maintenance/index'],
                        ['style' => 'text-decoration: none; color: inherit;']
                    ) ?>
                </div>
                <div class="col-md-3 col-sm-6">
                    <?= Html::a(
                        '<div class="small-box" style="background-color: var(--primary-color);">
                            <div class="inner">
                                <h3>' . ($stats['completed'] ?? 0) . '</h3>
                                <p>Concluídas</p>
                            </div>
                            <div class="icon"><i class="fas fa-check-circle"></i></div>
                        </div>',
                        ['dashboard/maintenance', 'status' => 'completed'],
                        ['style' => 'text-decoration: none; color: inherit;']
                    ) ?>
                </div>
                <div class="col-md-3 col-sm-6">
                    <?= Html::a(
                        '<div class="small-box" style="background-color: #28a745;">
                            <div class="inner text-white">
                                <h3>' . ($stats['overdue'] ?? 0) . '</h3>
                                <p>Atrasadas</p>
                            </div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>',
                        ['dashboard/maintenance', 'status' => 'overdue'],
                        ['style' => 'text-decoration: none; color: inherit;']
                    ) ?>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="small-box" style="background-color: #ffc107;">
                        <div class="inner">
                            <h3><?= number_format($stats['totalCost'] ?? 0, 2, ',', '.') ?> €</h3>
                            <p>Custo Total</p>
                        </div>
                        <div class="icon"><i class="fas fa-euro-sign"></i></div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Table -->
            <div class="card">
                <!-- Navigation Tabs -->
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <?= Html::a('Agendadas (' . $stats['scheduled'] . ')', ['dashboard/maintenance', 'status' => 'scheduled'], [
                                'class' => 'nav-link' . ($status === 'scheduled' ? ' active' : ''),
                            ]) ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a('Concluídas (' . $stats['completed'] . ')', ['dashboard/maintenance', 'status' => 'completed'], [
                                'class' => 'nav-link' . ($status === 'completed' ? ' active' : ''),
                            ]) ?>
                        </li>
                        <li class="nav-item">
                            <?= Html::a('Atrasadas (' . $stats['overdue'] . ')', ['dashboard/maintenance', 'status' => 'overdue'], [
                                'class' => 'nav-link' . ($status === 'overdue' ? ' active' : ''),
                            ]) ?>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
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
                                'attribute' => 'tipo',
                                'label' => 'Tipo',
                            ],
                            [
                                'attribute' => 'data',
                                'label' => 'Data',
                                'format' => ['date', 'php:d/m/Y'],
                            ],
                            [
                                'attribute' => 'custo',
                                'label' => 'Custo',
                                'value' => function($model) {
                                    return $model->custo !== null ? number_format($model->custo, 2, ',', '.') . ' €' : '-';
                                },
                            ],
                            [
                                'attribute' => 'oficina',
                                'label' => 'Oficina',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'header' => 'Ações',
                                'headerOptions' => ['style' => 'width:180px; text-align:center'],
                                'contentOptions' => ['style' => 'text-align:center; white-space:nowrap'],
                                'template' => $status === 'completed' ? '{view} {delete}' : '{view} {complete} {update} {delete}',
                                'buttons' => [
                                    'view' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.view')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-eye"></i>', ['maintenance/view', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-info',
                                            'title' => 'Ver',
                                        ]);
                                    },
                                    'complete' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.update')) {
                                            return '';
                                        }
                                        // Only show complete button if scheduled
                                        if ($model->status !== 'scheduled') {
                                            return '';
                                        }
                                        $csrfToken = Yii::$app->request->getCsrfToken();
                                        return Html::button('<i class="fas fa-check"></i>', [
                                            'class' => 'btn btn-sm btn-success',
                                            'title' => 'Concluir',
                                            'onclick' => "if (confirm('Tem a certeza que pretende marcar esta manutenção como concluída?')) { "
                                                . "var form = document.createElement('form'); "
                                                . "form.method = 'POST'; "
                                                . "form.action = '" . Yii::$app->urlManager->createUrl(['maintenance/complete', 'id' => $model->id]) . "'; "
                                                . "var input = document.createElement('input'); "
                                                . "input.type = 'hidden'; "
                                                . "input.name = '" . Yii::$app->request->csrfParam . "'; "
                                                . "input.value = '" . $csrfToken . "'; "
                                                . "form.appendChild(input); "
                                                . "document.body.appendChild(form); "
                                                . "form.submit(); "
                                                . "} return false;",
                                        ]);
                                    },
                                    'update' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.update')) {
                                            return '';
                                        }
                                        return Html::a('<i class="fas fa-edit"></i>', ['maintenance/update', 'id' => $model->id], [
                                            'class' => 'btn btn-sm btn-warning',
                                            'title' => 'Editar',
                                        ]);
                                    },
                                    'delete' => function($url, $model, $key) {
                                        if (!Yii::$app->user->can('maintenances.delete')) {
                                            return '';
                                        }
                                        $csrfToken = Yii::$app->request->getCsrfToken();
                                        return Html::button('<i class="fas fa-trash"></i>', [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'Eliminar',
                                            'onclick' => "if (confirm('Tem a certeza que pretende eliminar esta manutenção?')) { "
                                                . "var form = document.createElement('form'); "
                                                . "form.method = 'POST'; "
                                                . "form.action = '" . Yii::$app->urlManager->createUrl(['maintenance/delete', 'id' => $model->id]) . "'; "
                                                . "var input = document.createElement('input'); "
                                                . "input.type = 'hidden'; "
                                                . "input.name = '" . Yii::$app->request->csrfParam . "'; "
                                                . "input.value = '" . $csrfToken . "'; "
                                                . "form.appendChild(input); "
                                                . "document.body.appendChild(form); "
                                                . "form.submit(); "
                                                . "} return false;",
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]) ?>

                </div>
            </div>
        </div>
    </div>
</div>