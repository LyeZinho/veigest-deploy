<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Company;

/** @var $this yii\web\View */
/** @var $model common\models\User */
/** @var $form yii\widgets\ActiveForm */
/** @var $roles array */

// Obter lista de empresas
$companies = ArrayHelper::map(Company::find()->all(), 'id', 'name');
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'company_id')->dropDownList($companies, [
                'prompt' => 'Selecione uma empresa...'
            ])->label('Empresa') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'tempRole')->dropDownList($roles, [
                'prompt' => 'Selecione um papel...'
            ])->label('Papel') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('Nome Completo') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'type' => 'email']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'phone')->textInput(['maxlength' => true])->label('Telefone') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'password')->passwordInput(['maxlength' => true])->hint(
                $model->isNewRecord ? 'Palavra-passe deve ter pelo menos 3 caracteres' : 'Deixe em branco para manter a palavra-passe atual'
            ) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'status')->dropDownList([
                'active' => 'Ativo',
                'inactive' => 'Inativo',
            ], ['prompt' => 'Selecione o estado...']) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Criar' : 'Guardar', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>