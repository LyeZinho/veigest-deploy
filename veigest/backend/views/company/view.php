<?php

use yii\helpers\Html;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Empresas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-view">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1><?= Html::encode($this->title) ?></h1>
                <div>
                    <?= Html::a('Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                    <?= Html::a('Eliminar', ['delete', 'id' => $model->id], ['class' => 'btn btn-danger', 'data' => ['confirm' => 'Tem a certeza?', 'method' => 'post']]) ?>
                    <?= Html::a('Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Código</h5>
                            <p><?= Html::encode($model->code) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Nome</h5>
                            <p><?= Html::encode($model->name) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>NIF</h5>
                            <p><?= Html::encode($model->tax_id) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Email</h5>
                            <p><?= Html::encode($model->email) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Telefone</h5>
                            <p><?= Html::encode($model->phone) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Estado</h5>
                            <p>
                                <?php if ($model->status === 'active'): ?>
                                    <span class="badge badge-success">Ativo</span>
                                <?php elseif ($model->status === 'suspended'): ?>
                                    <span class="badge badge-warning">Suspenso</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inativo</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h5>Plano</h5>
                            <p>
                                <?php 
                                $plans = ['basic' => 'Básico', 'professional' => 'Profissional', 'enterprise' => 'Empresarial'];
                                echo Html::encode($plans[$model->plan] ?? $model->plan);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
