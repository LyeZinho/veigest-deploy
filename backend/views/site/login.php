<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Entrar';
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<div class="site-login">
    <div class="mt-5 offset-lg-3 col-lg-6">
        <h1><?= Html::encode($this->title) ?></h1>

        <p>Por favor preencha os seguintes campos para entrar:</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'autocomplete' => 'new-password', 'value' => ''])->label('Nome de Utilizador') ?>

            <div class="form-group field-loginform-password">
                <label for="loginform-password">Palavra-passe</label>
                <div class="input-group">
                    <?= Html::activePasswordInput($model, 'password', [
                        'autocomplete' => 'new-password',
                        'value' => '',
                        'id' => 'loginform-password',
                        'class' => 'form-control'
                    ]) ?>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <?php if ($model->hasErrors('password')): ?>
                    <div class="invalid-feedback d-block"><?= $model->getFirstError('password') ?></div>
                <?php endif; ?>
            </div>

            <?= $form->field($model, 'rememberMe')->checkbox()->label('Lembrar-me') ?>

            <div class="form-group">
                <?= Html::submitButton('Entrar', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var usernameField = document.querySelector('input[name="LoginForm[username]"]');
    var passwordField = document.querySelector('input[name="LoginForm[password]"]');
    
    if (usernameField) usernameField.value = '';
    if (passwordField) passwordField.value = '';

    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('loginform-password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        }
    });
});
</script>
