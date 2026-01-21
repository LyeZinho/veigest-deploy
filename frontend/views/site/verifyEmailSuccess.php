<?php
/** @var yii\web\View $this */
$this->title = 'Email verificado';
?>
<section class="min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-lg bg-white rounded-lg shadow-lg p-8 text-center">
    <h1 class="text-3xl font-bold mb-4">Parabéns!</h1>
    <p class="mb-6">O seu email foi verificado com sucesso!</p>
    <a href="/index-test.php?r=site%2Flogin" class="inline-block px-6 py-2 rounded bg-green-500 text-white">Ir para login</a>
  </div>
</section>
