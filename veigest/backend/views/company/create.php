<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Criar Empresa';
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-create">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="card">
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'Gerado automaticamente se deixado vazio'])->hint('Código único da empresa (gerado automaticamente se não preenchido)') ?>

                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'tax_id')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'email')->textInput(['type' => 'email', 'maxlength' => true]) ?>

                    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'status')->dropDownList(['active' => 'Ativo', 'suspended' => 'Suspenso', 'inactive' => 'Inativo']) ?>

                    <?= $form->field($model, 'plan')->dropDownList(['basic' => 'Básico', 'professional' => 'Profissional', 'enterprise' => 'Empresarial']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Criar', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
