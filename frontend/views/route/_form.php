<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\Route $model */
/** @var array $drivers */
/** @var array $vehicles */
?>

<div class="route-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users mr-2"></i>Atribuição</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'driver_id')->dropDownList($drivers, [
                        'prompt' => 'Selecione o condutor...', 
                        'class' => 'form-control'
                    ])->label('<i class="fas fa-user mr-1"></i> Condutor') ?>

                    <?= $form->field($model, 'vehicle_id')->dropDownList($vehicles, [
                        'prompt' => 'Selecione o veículo...',
                        'class' => 'form-control'
                    ])->label('<i class="fas fa-car mr-1"></i> Veículo') ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Horário</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'start_time')->input('datetime-local', [
                        'class' => 'form-control',
                        'value' => $model->start_time ? date('Y-m-d\TH:i', strtotime($model->start_time)) : date('Y-m-d\TH:i'),
                    ])->label('<i class="fas fa-play mr-1"></i> Data/Hora Início') ?>

                    <?= $form->field($model, 'end_time')->input('datetime-local', [
                        'class' => 'form-control',
                        'value' => $model->end_time ? date('Y-m-d\TH:i', strtotime($model->end_time)) : '',
                    ])->label('<i class="fas fa-stop mr-1"></i> Data/Hora Fim (opcional)') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-map-marked-alt mr-2"></i>Trajeto</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'start_location')->textInput([
                                'maxlength' => true, 
                                'class' => 'form-control',
                                'placeholder' => 'Ex: Lisboa, Av. da Liberdade'
                            ])->label('<i class="fas fa-map-marker-alt text-danger mr-1"></i> Origem') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'end_location')->textInput([
                                'maxlength' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Ex: Porto, Rua de Santa Catarina'
                            ])->label('<i class="fas fa-flag-checkered text-success mr-1"></i> Destino') ?>
                        </div>
                    </div>
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
                    $model->isNewRecord ? '<i class="fas fa-plus"></i> Criar Rota' : '<i class="fas fa-save"></i> Guardar Alterações',
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'style' => 'flex: 1;']
                ) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
