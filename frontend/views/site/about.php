<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Sobre Nós';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Esta é a página Sobre Nós. Pode modificar o seguinte ficheiro para personalizar o seu conteúdo:</p>

    <code><?= __FILE__ ?></code>
</div>
