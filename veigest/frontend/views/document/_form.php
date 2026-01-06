<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use common\models\Document;

/** @var yii\web\View $this */
/** @var frontend\models\DocumentUploadForm $model */
/** @var common\models\Vehicle[] $vehicles */
/** @var common\models\User[] $drivers */

?>

<div class="document-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'fieldConfig' => [
            'template' => "{label}\n{input}\n{hint}\n{error}",
            'labelOptions' => ['class' => 'form-label font-weight-bold'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback d-block'],
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'uploadedFile')->fileInput([
                'class' => 'form-control-file',
                'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif',
            ])->hint('Selecione um ficheiro para upload (máx. 10MB)') ?>
        </div>
    </div>

    <hr class="my-4">

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
        <?= Html::submitButton('<i class="fas fa-upload mr-2"></i>Enviar Documento', [
            'class' => 'btn btn-primary'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
