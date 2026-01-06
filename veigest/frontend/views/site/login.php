<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    :root {
        --color-primary: #09BC8A;
        --color-onyx: #3C3C3C;
        --color-turquoise: #75DDDD;
    }

    .bg-primary {
        background-color: var(--color-primary);
    }

    .text-primary {
        color: var(--color-primary);
    }

    .input-focus:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(9, 188, 138, 0.1);
    }

    #togglePassword {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        background: transparent;
        border: none;
        padding: 0 12px;
        cursor: pointer;
        color: #6b7280;
        transition: color 0.2s;
    }

    #togglePassword:hover {
        color: #111827;
    }

    #togglePassword:focus {
        outline: none;
    }
</style>

<div class="bg-gray-50 min-h-screen flex items-center justify-center px-4 pt-10 pb-10">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center space-x-2 mb-4">
                <img src="./images/veigest-logo.png" alt="VeiGest" class="h-12 w-12">
                <span class="text-2xl font-bold text-primary">VeiGest</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Aceder ao Sistema</h1>
            <p class="text-gray-600 mt-2">Gestão Inteligente de Frotas</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'options' => ['class' => 'space-y-6']
            ]); ?>
            <!-- Username -->
            <div>
                <?= $form->field($model, 'username')->textInput([
                    'autofocus' => true,
                    'autocomplete' => 'new-password',
                    'value' => '',
                    'class' => 'bg-white border border-gray-300 rounded-lg block w-full p-2.5 placeholder-gray-400 text-gray-900',
                    'placeholder' => 'Nome de Utilizador',
                ])->label('Nome de Utilizador', ['class' => 'text-sm font-medium text-gray-900']) ?>
            </div>

            <!-- Password -->
            <div>
                <label class="text-sm font-medium text-gray-900">Palavra-passe</label>
                <div class="relative">
                    <?= Html::activePasswordInput($model, 'password', [
                        'autocomplete' => 'new-password',
                        'value' => '',
                        'id' => 'loginform-password',
                        'class' => 'bg-white border border-gray-300 rounded-lg block w-full p-2.5 pr-10 placeholder-gray-400 text-gray-900',
                        'placeholder' => '••••••••',
                    ]) ?>
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-900">
                        <i class="bi bi-eye-slash" id="eyeIcon"></i>
                    </button>
                </div>
                <?php if ($model->hasErrors('password')): ?>
                    <div class="text-red-600 text-sm mt-1"><?= $model->getFirstError('password') ?></div>
                <?php endif; ?>
            </div>

            <!-- Remember & Forgot -->
            <div class="flex items-center justify-between">

                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox"
                        name="LoginForm[rememberMe]"
                        value="1"
                        <?= $model->rememberMe ? 'checked' : '' ?>
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <span class="text-sm text-gray-700">Lembrar-me</span>
                </label>

                <a href="<?= \yii\helpers\Url::to(['site/request-password-reset']) ?>"
                    class="text-sm font-medium text-primary hover:underline">
                    Esqueceu a palavra-passe?
                </a>

            </div>


            <!-- Login Button -->
            <div>
                <?= Html::submitButton('Entrar', [
                    'class' => 'w-full py-3 px-4 bg-primary text-white font-semibold rounded-lg hover:opacity-90 transition-all duration-300',
                    'style' => 'background-color: #09BC8A;'
                ]) ?>
            </div>
            <?php ActiveForm::end(); ?>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Não tem conta?</span>
                </div>
            </div>

            <!-- Sign Up -->
            <a href="<?= \yii\helpers\Url::to(['site/signup']) ?>" class="block w-full text-center border-2 border-primary text-primary py-3 rounded-lg font-bold hover:bg-primary hover:bg-opacity-5 transition">
                Criar Conta
            </a>
        </div>

        <div class="mt-8 grid grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4 text-center">
                <i class="fas fa-lock text-2xl text-primary mb-2"></i>
                <p class="text-xs text-gray-600">Segurança em Primeiro Lugar</p>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
                <i class="fas fa-mobile-alt text-2xl text-primary mb-2"></i>
                <p class="text-xs text-gray-600">Acesso Móvel</p>
            </div>
            <div class="bg-white rounded-lg p-4 text-center">
                <i class="fas fa-headset text-2xl text-primary mb-2"></i>
                <p class="text-xs text-gray-600">Suporte 24/7</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm text-gray-600">
            <p>Problemas ao aceder? <a href="<?= \yii\helpers\Url::to(['site/contact']) ?>" class="text-primary hover:underline">Contacte o suporte</a></p>
        </div>
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