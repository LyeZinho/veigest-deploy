<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use common\models\Document;

/** @var yii\web\View $this */
/** @var common\models\Document $model */
/** @var common\models\Vehicle[] $vehicles */
/** @var common\models\User[] $drivers */

$this->title = 'Editar Documento: ' . ($model->file ? $model->file->original_name : 'Documento #' . $model->id);
$this->params['breadcrumbs'][] = ['label' => 'Gestão Documental', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Ver Documento', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-edit mr-2"></i>Editar Documento
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Voltar', ['index'], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt mr-2"></i>Dados do Documento
                        </h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- Informação do ficheiro atual -->
                        <?php if ($model->file): ?>
                        <div class="alert alert-info">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas <?= $model->file->getFileIcon() ?> fa-2x"></i>
                                </div>
                                <div class="col">
                                    <strong><?= Html::encode($model->file->original_name) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= $model->file->getFormattedSize() ?> | 
                                        Enviado em <?= Yii::$app->formatter->asDatetime($model->file->created_at, 'dd/MM/yyyy HH:mm') ?>
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <?= Html::a('<i class="fas fa-download"></i> Descarregar', 
                                        ['download', 'id' => $model->id], 
                                        ['class' => 'btn btn-sm btn-info']
                                    ) ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php $form = ActiveForm::begin([
                            'fieldConfig' => [
                                'template' => "{label}\n{input}\n{hint}\n{error}",
                                'labelOptions' => ['class' => 'form-label font-weight-bold'],
                                'inputOptions' => ['class' => 'form-control'],
                                'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            ],
                        ]); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'type')->dropDownList(
                                    Document::getTypesList(),
                                    [
                                        'prompt' => '-- Selecione o tipo --',
                                        'class' => 'form-control',
                                    ]
                                ) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'expiry_date')->input('date', [
                                    'class' => 'form-control',
                                ])->hint('Deixe em branco se o documento não expira') ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'vehicle_id')->dropDownList(
                                    ArrayHelper::map($vehicles, 'id', function($vehicle) {
                                        return $vehicle->license_plate . ' - ' . $vehicle->brand . ' ' . $vehicle->model;
                                    }),
                                    [
                                        'prompt' => '-- Selecione um veículo (opcional) --',
                                        'class' => 'form-control',
                                    ]
                                ) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'driver_id')->dropDownList(
                                    ArrayHelper::map($drivers, 'id', 'name'),
                                    [
                                        'prompt' => '-- Selecione um motorista (opcional) --',
                                        'class' => 'form-control',
                                    ]
                                ) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?= $form->field($model, 'notes')->textarea([
                                    'rows' => 3,
                                    'placeholder' => 'Adicione observações sobre o documento...',
                                ]) ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="form-group text-right">
                            <?= Html::a('<i class="fas fa-times mr-2"></i>Cancelar', ['index'], [
                                'class' => 'btn btn-secondary mr-2'
                            ]) ?>
                            <?= Html::submitButton('<i class="fas fa-save mr-2"></i>Guardar Alterações', [
                                'class' => 'btn btn-primary'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>

                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>Estado Atual
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="badge <?= $model->getStatusBadgeClass() ?> p-2" style="font-size: 1rem;">
                                <?= $model->getStatusDisplayLabel() ?>
                            </span>
                        </div>
                        
                        <?php if ($model->expiry_date): ?>
                            <?php $days = $model->getDaysUntilExpiry(); ?>
                            <p class="text-center text-muted">
                                <?php if ($days > 0): ?>
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Expira em <strong><?= $days ?></strong> dias
                                <?php elseif ($days === 0): ?>
                                    <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                    Expira <strong>hoje</strong>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger mr-1"></i>
                                    Expirou há <strong><?= abs($days) ?></strong> dias
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <hr>

                        <h6><i class="fas fa-history mr-2 text-primary"></i>Histórico</h6>
                        <ul class="list-unstyled small text-muted">
                            <li>
                                <i class="fas fa-plus-circle mr-1"></i>
                                Criado: <?= Yii::$app->formatter->asDatetime($model->created_at, 'dd/MM/yyyy HH:mm') ?>
                            </li>
                            <?php if ($model->updated_at): ?>
                            <li>
                                <i class="fas fa-edit mr-1"></i>
                                Atualizado: <?= Yii::$app->formatter->asDatetime($model->updated_at, 'dd/MM/yyyy HH:mm') ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trash mr-2"></i>Zona de Perigo
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Esta ação é irreversível e eliminará o documento e o ficheiro associado.</p>
                        <?= Html::a('<i class="fas fa-trash mr-2"></i>Eliminar Documento', 
                            ['delete', 'id' => $model->id], 
                            [
                                'class' => 'btn btn-danger btn-block',
                                'data' => [
                                    'confirm' => 'Tem a certeza que deseja eliminar este documento? Esta ação não pode ser desfeita.',
                                    'method' => 'post',
                                ],
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
