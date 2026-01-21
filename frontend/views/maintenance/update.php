<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\Maintenance $model */
/** @var array $vehicles */

$this->title = 'Editar Manutenção: ' . $model->vehicle->model;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Manutenções', 'url' => ['maintenance/index']];
$this->params['breadcrumbs'][] = ['label' => $model->vehicle->model, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-11 col-xxl-10">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11 col-xxl-10">
            <div class="card">
                <div class="card-body">
                    <?= $this->render('_form', [
                        'model' => $model,
                        'vehicles' => $vehicles,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
