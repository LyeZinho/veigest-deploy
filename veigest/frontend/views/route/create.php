<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Route $model */
/** @var array $drivers */
/** @var array $vehicles */

$this->title = 'Nova Rota';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Rotas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-plus-circle mr-2"></i><?= Html::encode($this->title) ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Rotas</a></li>
                        <li class="breadcrumb-item active"><?= Html::encode($this->title) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar', ['index'], ['class' => 'btn btn-secondary mb-3']) ?>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="card card-primary card-outline" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-route mr-2"></i>Dados da Rota
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <?= $this->render('_form', compact('model', 'drivers', 'vehicles')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
