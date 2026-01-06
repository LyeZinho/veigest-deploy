<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */
/** @var yii\data\ActiveDataProvider $maintenanceProvider */
/** @var yii\data\ActiveDataProvider $fuelProvider */
/** @var string $activeTab */

$this->title = 'Histórico - ' . $model->license_plate;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->license_plate, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Histórico';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-history mr-2"></i>Histórico do Veículo
                    </h1>
                    <small class="text-muted"><?= Html::encode($model->brand . ' ' . $model->model . ' - ' . $model->license_plate) ?></small>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Veículos</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->license_plate) ?></a></li>
                        <li class="breadcrumb-item active">Histórico</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Botões de Navegação -->
            <div class="row mb-3">
                <div class="col-12">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar ao Veículo', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>

            <!-- Tabs de Histórico -->
            <div class="card card-primary card-outline card-outline-tabs">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs" id="history-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?= $activeTab === 'maintenance' ? 'active' : '' ?>" 
                               id="maintenance-tab" 
                               data-toggle="pill" 
                               href="#maintenance" 
                               role="tab">
                                <i class="fas fa-wrench mr-1"></i> Manutenções
                                <span class="badge badge-warning ml-1"><?= $maintenanceProvider->getTotalCount() ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $activeTab === 'fuel' ? 'active' : '' ?>" 
                               id="fuel-tab" 
                               data-toggle="pill" 
                               href="#fuel" 
                               role="tab">
                                <i class="fas fa-gas-pump mr-1"></i> Abastecimentos
                                <span class="badge badge-danger ml-1"><?= $fuelProvider->getTotalCount() ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="history-tabs-content">
                        <!-- Tab Manutenções -->
                        <div class="tab-pane fade <?= $activeTab === 'maintenance' ? 'show active' : '' ?>" id="maintenance" role="tabpanel">
                            <?php if ($maintenanceProvider->getTotalCount() > 0): ?>
                                <?= GridView::widget([
                                    'dataProvider' => $maintenanceProvider,
                                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                                    'columns' => [
                                        ['class' => 'yii\grid\SerialColumn'],
                                        [
                                            'attribute' => 'type',
                                            'label' => 'Tipo',
                                            'value' => function($model) {
                                                $types = [
                                                    'preventiva' => '<span class="badge badge-info"><i class="fas fa-shield-alt"></i> Preventiva</span>',
                                                    'corretiva' => '<span class="badge badge-warning"><i class="fas fa-tools"></i> Corretiva</span>',
                                                    'revisao' => '<span class="badge badge-primary"><i class="fas fa-search"></i> Revisão</span>',
                                                    'outro' => '<span class="badge badge-secondary"><i class="fas fa-ellipsis-h"></i> Outro</span>',
                                                ];
                                                return $types[$model->type ?? 'outro'] ?? '<span class="badge badge-secondary">' . Html::encode($model->type) . '</span>';
                                            },
                                            'format' => 'html',
                                        ],
                                        [
                                            'attribute' => 'description',
                                            'label' => 'Descrição',
                                            'value' => function($model) {
                                                return Html::encode(mb_substr($model->description ?? '', 0, 50)) . (mb_strlen($model->description ?? '') > 50 ? '...' : '');
                                            },
                                        ],
                                        [
                                            'attribute' => 'date',
                                            'label' => 'Data',
                                            'value' => function($model) {
                                                return Yii::$app->formatter->asDate($model->date ?? $model->created_at, 'php:d/m/Y');
                                            },
                                        ],
                                        [
                                            'attribute' => 'mileage_record',
                                            'label' => 'Km',
                                            'value' => function($model) {
                                                return $model->mileage_record ? number_format($model->mileage_record, 0, ',', '.') . ' km' : '-';
                                            },
                                        ],
                                        [
                                            'attribute' => 'cost',
                                            'label' => 'Custo',
                                            'value' => function($model) {
                                                return number_format($model->cost ?? 0, 2, ',', '.') . ' €';
                                            },
                                        ],
                                        [
                                            'attribute' => 'status',
                                            'label' => 'Estado',
                                            'value' => function($model) {
                                                $statuses = [
                                                    'pendente' => '<span class="badge badge-warning">Pendente</span>',
                                                    'em_curso' => '<span class="badge badge-info">Em Curso</span>',
                                                    'concluida' => '<span class="badge badge-success">Concluída</span>',
                                                    'cancelada' => '<span class="badge badge-danger">Cancelada</span>',
                                                ];
                                                return $statuses[$model->status ?? 'concluida'] ?? '<span class="badge badge-secondary">' . Html::encode($model->status) . '</span>';
                                            },
                                            'format' => 'html',
                                        ],
                                    ],
                                ]) ?>

                                <!-- Resumo de Manutenções -->
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="info-box bg-gradient-warning">
                                            <span class="info-box-icon"><i class="fas fa-wrench"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total de Manutenções</span>
                                                <span class="info-box-number"><?= $maintenanceProvider->getTotalCount() ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-gradient-success">
                                            <span class="info-box-icon"><i class="fas fa-euro-sign"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Custo Total</span>
                                                <span class="info-box-number">
                                                    <?php
                                                    $totalCost = 0;
                                                    foreach ($maintenanceProvider->getModels() as $m) {
                                                        $totalCost += $m->cost ?? 0;
                                                    }
                                                    echo number_format($totalCost, 2, ',', '.') . ' €';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-box bg-gradient-info">
                                            <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Última Manutenção</span>
                                                <span class="info-box-number">
                                                    <?php
                                                    $models = $maintenanceProvider->getModels();
                                                    if (!empty($models)) {
                                                        echo Yii::$app->formatter->asDate($models[0]->date ?? $models[0]->created_at, 'php:d/m/Y');
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                                    <h4>Nenhuma manutenção registada</h4>
                                    <p class="text-muted">Este veículo ainda não possui histórico de manutenções.</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tab Abastecimentos -->
                        <div class="tab-pane fade <?= $activeTab === 'fuel' ? 'show active' : '' ?>" id="fuel" role="tabpanel">
                            <?php if ($fuelProvider->getTotalCount() > 0): ?>
                                <?= GridView::widget([
                                    'dataProvider' => $fuelProvider,
                                    'tableOptions' => ['class' => 'table table-striped table-bordered'],
                                    'columns' => [
                                        ['class' => 'yii\grid\SerialColumn'],
                                        [
                                            'attribute' => 'date',
                                            'label' => 'Data',
                                            'value' => function($model) {
                                                return Yii::$app->formatter->asDate($model->date ?? $model->created_at, 'php:d/m/Y');
                                            },
                                        ],
                                        [
                                            'attribute' => 'fuel_type',
                                            'label' => 'Combustível',
                                            'value' => function($model) {
                                                $types = [
                                                    'gasolina' => '<i class="fas fa-gas-pump text-danger"></i> Gasolina',
                                                    'diesel' => '<i class="fas fa-gas-pump text-warning"></i> Diesel',
                                                    'eletrico' => '<i class="fas fa-bolt text-success"></i> Elétrico',
                                                    'gpl' => '<i class="fas fa-fire text-primary"></i> GPL',
                                                ];
                                                return $types[$model->fuel_type ?? ''] ?? Html::encode($model->fuel_type ?? '-');
                                            },
                                            'format' => 'html',
                                        ],
                                        [
                                            'attribute' => 'liters',
                                            'label' => 'Litros',
                                            'value' => function($model) {
                                                return $model->liters ? number_format($model->liters, 2, ',', '.') . ' L' : '-';
                                            },
                                        ],
                                        [
                                            'attribute' => 'price_per_liter',
                                            'label' => 'Preço/L',
                                            'value' => function($model) {
                                                return $model->price_per_liter ? number_format($model->price_per_liter, 3, ',', '.') . ' €' : '-';
                                            },
                                        ],
                                        [
                                            'attribute' => 'value',
                                            'label' => 'Custo Total',
                                            'value' => function($model) {
                                                return $model->value ? number_format($model->value, 2, ',', '.') . ' €' : '-';
                                            },
                                        ],
                                        [
                                            'attribute' => 'current_mileage',
                                            'label' => 'Km no Abastecimento',
                                            'value' => function($model) {
                                                return $model->current_mileage ? number_format($model->current_mileage, 0, ',', '.') . ' km' : '-';
                                            },
                                        ],

                                    ],
                                ]) ?>

                                <!-- Resumo de Abastecimentos -->
                                <div class="row mt-4">
                                    <div class="col-md-3">
                                        <div class="info-box bg-gradient-danger">
                                            <span class="info-box-icon"><i class="fas fa-gas-pump"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Abastecimentos</span>
                                                <span class="info-box-number"><?= $fuelProvider->getTotalCount() ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-gradient-primary">
                                            <span class="info-box-icon"><i class="fas fa-tint"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Total Litros</span>
                                                <span class="info-box-number">
                                                    <?php
                                                    $totalLiters = 0;
                                                    foreach ($fuelProvider->getModels() as $f) {
                                                        $totalLiters += $f->liters;
                                                    }
                                                    echo number_format($totalLiters, 2, ',', '.') . ' L';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-gradient-success">
                                            <span class="info-box-icon"><i class="fas fa-euro-sign"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Custo Total</span>
                                                <span class="info-box-number">
                                                    <?php
                                                    $totalCost = 0;
                                                    foreach ($fuelProvider->getModels() as $f) {
                                                        $totalCost += $f->value;
                                                    }
                                                    echo number_format($totalCost, 2, ',', '.') . ' €';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box bg-gradient-info">
                                            <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Média €/L</span>
                                                <span class="info-box-number">
                                                    <?php
                                                    $totalLiters = 0;
                                                    $totalCost = 0;
                                                    foreach ($fuelProvider->getModels() as $f) {
                                                        $totalLiters += $f->liters;
                                                        $totalCost += $f->value;
                                                    }
                                                    echo $totalLiters > 0 ? number_format($totalCost / $totalLiters, 3, ',', '.') . ' €' : '-';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-gas-pump fa-4x text-muted mb-3"></i>
                                    <h4>Nenhum abastecimento registado</h4>
                                    <p class="text-muted">Este veículo ainda não possui histórico de abastecimentos.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
