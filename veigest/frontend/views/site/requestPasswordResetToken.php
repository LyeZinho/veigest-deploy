<?php
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\widgets\Alert;

$this->title = 'Recuperar Palavra-passe';
?>

<section class="bg-gray-50 dark:bg-gray-900 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md bg-white rounded-lg shadow dark:border dark:bg-gray-800 dark:border-gray-700">
      <div class="p-6 space-y-6">
          <h1 class="text-2xl font-bold text-center text-gray-900 dark:text-white"><?= Html::encode($this->title) ?></h1>
          <p class="text-center text-gray-600 dark:text-gray-400 text-sm mb-4">Insira o seu email para receber o link de redefinição.</p>

          <?php // Mostrar flashes (success / error) ?>
          <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
              <div class="mb-3">
                  <div class="alert alert-<?= $type === 'error' ? 'danger' : $type; ?>" role="alert">
                      <?= Html::encode($message) ?>
                  </div>
              </div>
          <?php endforeach; ?>

          <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>

              <?= $form->errorSummary($model) // mostra erros de validação se existirem ?>

              <?= $form->field($model, 'email')->textInput([
                  'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white',
              ])->label('Email', ['class' => 'text-sm font-medium text-gray-900 dark:text-white']) ?>

              <div class="form-group text-center">
                  <?= Html::submitButton('Enviar', ['class' => 'w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5']) ?>
              </div>

          <?php ActiveForm::end(); ?>
      </div>
  </div>
</section>
