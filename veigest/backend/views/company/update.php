<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Editar Empresa: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="company-update">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="card">
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => true])->hint('Código único da empresa (não pode ser alterado)') ?>

                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'tax_id')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'email')->textInput(['type' => 'email', 'maxlength' => true]) ?>

                    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'status')->dropDownList(['active' => 'Ativo', 'suspended' => 'Suspenso', 'inactive' => 'Inativo']) ?>

                    <?= $form->field($model, 'plan')->dropDownList(['basic' => 'Básico', 'professional' => 'Profissional', 'enterprise' => 'Empresarial']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Guardar', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Cancelar', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
