<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Driver;

/** @var yii\web\View $this */
/** @var frontend\models\Driver $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="driver-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-2"></i>Dados Pessoais</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'name')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Ex: João Silva', 
                        'class' => 'form-control'
                    ])->label('Nome Completo') ?>

                    <?= $form->field($model, 'email')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Ex: joao@email.com', 
                        'class' => 'form-control',
                        'type' => 'email',
                    ]) ?>

                    <?= $form->field($model, 'phone')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Ex: +351 91 1234567', 
                        'class' => 'form-control'
                    ])->label('Telefone') ?>

                    <?= $form->field($model, 'status')->dropDownList(
                        Driver::optsStatus(),
                        ['prompt' => 'Selecione o estado...', 'class' => 'form-control']
                    )->label('Estado') ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Carta de Condução</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'license_number')->textInput([
                        'maxlength' => true, 
                        'placeholder' => 'Ex: PT-123456789', 
                        'class' => 'form-control'
                    ])->label('Número da Carta') ?>

                    <?= $form->field($model, 'license_expiry')->textInput([
                        'type' => 'date', 
                        'class' => 'form-control'
                    ])->label('Validade da Carta') ?>

                    <?php if (!$model->isNewRecord && $model->license_expiry): ?>
                        <div class="alert alert-<?= $model->isLicenseValid() === false ? 'danger' : ($model->getDaysUntilLicenseExpiry() <= 30 ? 'warning' : 'success') ?> mb-0">
                            <i class="fas fa-<?= $model->isLicenseValid() === false ? 'exclamation-circle' : ($model->getDaysUntilLicenseExpiry() <= 30 ? 'clock' : 'check-circle') ?> mr-2"></i>
                            <?php if ($model->isLicenseValid() === false): ?>
                                <strong>Carta Expirada!</strong> A carta de condução expirou há <?= abs($model->getDaysUntilLicenseExpiry()) ?> dias.
                            <?php elseif ($model->getDaysUntilLicenseExpiry() <= 30): ?>
                                <strong>Atenção!</strong> A carta expira em <?= $model->getDaysUntilLicenseExpiry() ?> dias.
                            <?php else: ?>
                                <strong>Válida</strong> - Expira em <?= $model->getDaysUntilLicenseExpiry() ?> dias.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Segurança</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'password')->passwordInput([
                        'placeholder' => $model->isNewRecord ? 'Palavra-passe (mín. 6 caracteres)' : 'Deixe em branco para manter a atual', 
                        'class' => 'form-control',
                        'autocomplete' => 'new-password',
                    ])->label($model->isNewRecord ? 'Palavra-passe' : 'Nova Palavra-passe (opcional)') ?>
                    
                    <?php if ($model->isNewRecord): ?>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Se não definir uma palavra-passe, será gerada uma automaticamente.
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="btn-group btn-group-lg w-100" role="group">
                <?= Html::a(
                    '<i class="fas fa-times"></i> Cancelar',
                    $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id],
                    ['class' => 'btn btn-secondary']
                ) ?>
                <?= Html::submitButton(
                    $model->isNewRecord ? '<i class="fas fa-plus"></i> Criar Condutor' : '<i class="fas fa-save"></i> Guardar Alterações',
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'style' => 'flex: 1;']
                ) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
