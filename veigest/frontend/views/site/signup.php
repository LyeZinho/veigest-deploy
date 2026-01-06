<?php
/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\SignupForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

// Força o layout 'login' (usa o frontend/views/layouts/login.php)
$this->context->layout = 'login';

$this->title = 'Registar';
?>
<section class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md">
      <div class="text-center mb-6">
          <h1 style="position:absolute;left:-9999px;top:-9999px;">Registar</h1>
          <p style="position:absolute;left:-9999px;top:-9999px;">Por favor preencha os seguintes campos para se registar:</p>
          <img src="<?= Yii::getAlias('@web/images/veigest-logo.png') ?>"alt="VeiGest Logo" class="w-16 h-16 mx-auto mb-3">
          <h1 class="text-2xl font-semibold text-gray-900">VeiGest</h1>
          <p class="text-gray-500 text-sm">Crie a sua conta</p>
      </div>

      <div class="bg-white rounded-lg shadow-lg p-8">
          <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

              <?= $form->field($model, 'nome')->textInput([
                  'placeholder' => 'Nome completo',
                  'class' => 'bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5'
              ])->label(false) ?>

              <?= $form->field($model, 'username')->textInput([
                  'autofocus' => true,
                  'placeholder' => 'Nome de utilizador',
                  'class' => 'bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5'
              ])->label(false) ?>

              <?= $form->field($model, 'email')->textInput([
                  'placeholder' => 'Email',
                  'class' => 'bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5'
              ])->label(false) ?>

              <?= $form->field($model, 'password')->passwordInput([
                  'placeholder' => 'Palavra-passe',
                  'class' => 'bg-white border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5'
              ])->label(false) ?>

              <?= Html::submitButton('Registar', [
                  'class' => 'w-full text-white bg-primary hover:opacity-95 focus:ring-4 focus:outline-none font-medium rounded-lg text-sm px-5 py-2.5',
                  'style' => 'background-color: #09BC8A;'
              ]) ?>

          <?php ActiveForm::end(); ?>

          <p class="text-sm text-gray-500 text-center mt-4">
              Já tem conta?
              <?= Html::a('Entrar', ['site/login'], ['class' => 'font-medium text-primary hover:underline', 'style'=>'color:#09BC8A']) ?>
          </p>
      </div>

      <div class="mt-6 grid grid-cols-3 gap-4">
          <div class="text-center">
              <i class="fas fa-lock text-2xl" style="color:#09BC8A;"></i>
              <p class="text-xs text-gray-600">Segurança</p>
          </div>
          <div class="text-center">
              <i class="fas fa-mobile-alt text-2xl" style="color:#09BC8A;"></i>
              <p class="text-xs text-gray-600">Acesso Móvel</p>
          </div>
          <div class="text-center">
              <i class="fas fa-headset text-2xl" style="color:#09BC8A;"></i>
              <p class="text-xs text-gray-600">Suporte 24/7</p>
          </div>
      </div>
  </div>
</section>
