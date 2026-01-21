<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */
/** @var array $drivers */

$this->title = 'Atribuir Condutor - ' . $model->license_plate;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->license_plate, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Atribuir Condutor';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-user-plus mr-2"></i>Atribuir Condutor
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Veículos</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->license_plate) ?></a></li>
                        <li class="breadcrumb-item active">Atribuir Condutor</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Formulário de Atribuição -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user-check mr-2"></i>Selecionar Condutor</h3>
                        </div>
                        <?php $form = ActiveForm::begin(); ?>
                        <div class="card-body">
                            <?php if ($model->driver): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Condutor atual:</strong> <?= Html::encode($model->driver->name ?? $model->driver->username) ?> (<?= Html::encode($model->driver->email) ?>)
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="driver_id"><i class="fas fa-user mr-1"></i> Condutor</label>
                                <?= Html::dropDownList('driver_id', $model->driver_id, 
                                    ArrayHelper::map($drivers, 'id', function($driver) {
                                        return $driver->name ?? $driver->username . ' (' . $driver->email . ')';
                                    }),
                                    [
                                        'class' => 'form-control select2',
                                        'prompt' => '-- Nenhum condutor (remover atribuição) --',
                                        'id' => 'driver_id',
                                    ]
                                ) ?>
                                <small class="form-text text-muted">
                                    Selecione um condutor para atribuir a este veículo ou deixe em branco para remover a atribuição atual.
                                </small>
                            </div>

                            <?php if (empty($drivers)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    <strong>Atenção:</strong> Não há condutores disponíveis para atribuição. 
                                    <?php if (Yii::$app->user->can('drivers.create')): ?>
                                        <?= Html::a('Criar novo condutor', ['driver/create'], ['class' => 'alert-link']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <?= Html::a('<i class="fas fa-arrow-left"></i> Cancelar', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Atribuição', ['class' => 'btn btn-primary']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <!-- Info do Veículo -->
                <div class="col-md-4">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-car mr-2"></i>Veículo</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-car fa-4x text-primary"></i>
                            </div>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>Matrícula:</th>
                                    <td><strong><?= Html::encode($model->license_plate) ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Marca:</th>
                                    <td><?= Html::encode($model->brand) ?></td>
                                </tr>
                                <tr>
                                    <th>Modelo:</th>
                                    <td><?= Html::encode($model->model) ?></td>
                                </tr>
                                <tr>
                                    <th>Ano:</th>
                                    <td><?= Html::encode($model->year) ?></td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <?php
                                        $statusBadges = [
                                            'ativo' => '<span class="badge badge-success">Ativo</span>',
                                            'inativo' => '<span class="badge badge-secondary">Inativo</span>',
                                            'manutencao' => '<span class="badge badge-warning">Manutenção</span>',
                                        ];
                                        echo $statusBadges[$model->status] ?? $model->status;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Km:</th>
                                    <td><?= number_format($model->mileage, 0, ',', '.') ?> km</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Lista de Condutores Disponíveis -->
                    <?php if (!empty($drivers)): ?>
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-users mr-2"></i>Condutores Disponíveis</h3>
                        </div>
                        <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($drivers as $driver): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center <?= $model->driver_id == $driver->id ? 'list-group-item-primary' : '' ?>">
                                        <div>
                                            <i class="fas fa-user mr-2"></i>
                                            <strong><?= Html::encode($driver->name ?? $driver->username) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= Html::encode($driver->email) ?></small>
                                        </div>
                                        <?php if ($model->driver_id == $driver->id): ?>
                                            <span class="badge badge-primary">Atual</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Adicionar Select2 se disponível
$this->registerJs(<<<JS
    if (typeof $.fn.select2 !== 'undefined') {
        $('#driver_id').select2({
            theme: 'bootstrap4',
            placeholder: 'Selecione um condutor...',
            allowClear: true
        });
    }
JS
);
?>
