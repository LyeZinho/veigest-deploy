<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */
/** @var array $drivers */

$this->title = 'Editar Veículo: ' . $model->license_plate;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->license_plate, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-edit mr-2"></i>Editar Veículo
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Veículos</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->license_plate) ?></a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary mb-3']) ?>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="card card-warning card-outline" style="box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-car mr-2"></i>Editar: <?= Html::encode($model->license_plate) ?>
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <?= $this->render('_form', [
                                'model' => $model,
                                'drivers' => $drivers ?? [],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
