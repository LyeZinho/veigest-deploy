<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use frontend\models\Maintenance;

/** @var yii\web\View $this */
/** @var frontend\models\Maintenance $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $vehicles */
?>

<div class="maintenance-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'vehicle_id')->dropDownList(
                array_combine(
                    array_map(function($v) { return $v['id']; }, $vehicles),
                    array_map(function($v) { return $v['modelo'] . ' (' . $v['matricula'] . ')'; }, $vehicles)
                ),
                ['prompt' => 'Selecione um veículo']
            )->label('Veículo') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'tipo')->dropDownList(Maintenance::getTypes(), ['prompt' => 'Selecione o tipo'])->label('Tipo de Manutenção') ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'data')->input('date')->label('Data') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'oficina')->textInput(['maxlength' => true])->label('Oficina') ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-6">
            <?= $form->field($model, 'custo')->textInput(['type' => 'number', 'placeholder' => '0.00', 'step' => '0.01'])->label('Custo (€)') ?>
        </div>
        <div class="col-md-6">
            <!-- Quilometragem será preenchida automaticamente -->
        </div>
    </div>

    <div class="form-group row">
        <div class="col-md-12">
            <?= $form->field($model, 'descricao')->textarea(['rows' => 4, 'placeholder' => 'Descreva os detalhes da manutenção...'])->label('Descrição') ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Criar Manutenção' : 'Atualizar Manutenção', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
