<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\ChangePasswordForm $model */

$this->title = 'Alterar Palavra-passe';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Meu Perfil', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-key mr-2"></i><?= Html::encode($this->title) ?>
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
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-lock mr-2"></i>Alterar Palavra-passe
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'id' => 'change-password-form',
                            'fieldConfig' => [
                                'template' => "{label}\n{input}\n{hint}\n{error}",
                                'labelOptions' => ['class' => 'form-label font-weight-bold'],
                                'inputOptions' => ['class' => 'form-control'],
                                'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            ],
                        ]); ?>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            Por segurança, insira a sua palavra-passe atual antes de definir uma nova.
                        </div>

                        <?= $form->field($model, 'currentPassword')->passwordInput([
                            'placeholder' => 'Insira a palavra-passe atual',
                            'autocomplete' => 'current-password'
                        ]) ?>

                        <hr class="my-4">

                        <?= $form->field($model, 'newPassword')->passwordInput([
                            'placeholder' => 'Insira a nova palavra-passe',
                            'autocomplete' => 'new-password',
                            'id' => 'new-password'
                        ])->hint('Mínimo 6 caracteres. Deve conter maiúsculas, minúsculas e números.') ?>

                        <div class="password-strength mb-3" id="password-strength" style="display: none;">
                            <small class="text-muted">Força da palavra-passe:</small>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" id="strength-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="strength-text" class="text-muted"></small>
                        </div>

                        <?= $form->field($model, 'confirmPassword')->passwordInput([
                            'placeholder' => 'Confirme a nova palavra-passe',
                            'autocomplete' => 'new-password'
                        ]) ?>

                        <hr class="my-4">

                        <div class="form-group text-right">
                            <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary mr-2']) ?>
                            <?= Html::submitButton('<i class="fas fa-save mr-2"></i>Alterar Palavra-passe', [
                                'class' => 'btn btn-warning'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Card de Requisitos -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-list-ul mr-2"></i>Requisitos
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0" id="requirements-list">
                            <li class="mb-2" id="req-length">
                                <i class="fas fa-times-circle text-danger mr-2"></i>
                                Mínimo 6 caracteres
                            </li>
                            <li class="mb-2" id="req-uppercase">
                                <i class="fas fa-times-circle text-danger mr-2"></i>
                                Pelo menos uma letra maiúscula
                            </li>
                            <li class="mb-2" id="req-lowercase">
                                <i class="fas fa-times-circle text-danger mr-2"></i>
                                Pelo menos uma letra minúscula
                            </li>
                            <li id="req-number">
                                <i class="fas fa-times-circle text-danger mr-2"></i>
                                Pelo menos um número
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Card de Segurança -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-shield-alt mr-2"></i>Dicas de Segurança
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li class="mb-2">Nunca use a mesma palavra-passe em vários sites.</li>
                            <li class="mb-2">Evite informações pessoais como datas de nascimento.</li>
                            <li class="mb-2">Considere usar um gestor de palavras-passe.</li>
                            <li>Altere a palavra-passe regularmente.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$js = <<<JS
// Validação visual de requisitos da palavra-passe
document.getElementById('new-password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthDiv = document.getElementById('password-strength');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    
    if (password.length > 0) {
        strengthDiv.style.display = 'block';
    } else {
        strengthDiv.style.display = 'none';
    }
    
    // Verificar requisitos
    const hasLength = password.length >= 6;
    const hasUppercase = /[A-Z]/.test(password);
    const hasLowercase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    
    // Atualizar lista de requisitos
    updateRequirement('req-length', hasLength);
    updateRequirement('req-uppercase', hasUppercase);
    updateRequirement('req-lowercase', hasLowercase);
    updateRequirement('req-number', hasNumber);
    
    // Calcular força
    let strength = 0;
    if (hasLength) strength += 25;
    if (hasUppercase) strength += 25;
    if (hasLowercase) strength += 25;
    if (hasNumber) strength += 25;
    
    strengthBar.style.width = strength + '%';
    
    if (strength < 50) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Fraca';
        strengthText.className = 'text-danger';
    } else if (strength < 100) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Média';
        strengthText.className = 'text-warning';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Forte';
        strengthText.className = 'text-success';
    }
});

function updateRequirement(id, met) {
    const element = document.getElementById(id);
    const icon = element.querySelector('i');
    
    if (met) {
        icon.className = 'fas fa-check-circle text-success mr-2';
    } else {
        icon.className = 'fas fa-times-circle text-danger mr-2';
    }
}
JS;
$this->registerJs($js);
?>

<style>
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.form-label {
    color: #495057;
}
</style>
