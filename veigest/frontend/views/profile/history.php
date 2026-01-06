<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Histórico de Alterações';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Meu Perfil', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-history mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Voltar ao Perfil', ['index'], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Todas as Alterações
                </h3>
            </div>
            <div class="card-body p-0">
                <?php Pjax::begin(); ?>
                
                <?php if ($dataProvider->totalCount > 0): ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'layout' => "{items}\n<div class='card-footer clearfix'>{summary}\n{pager}</div>",
                        'tableOptions' => ['class' => 'table table-hover table-striped mb-0'],
                        'columns' => [
                            [
                                'attribute' => 'created_at',
                                'label' => 'Data/Hora',
                                'format' => 'datetime',
                                'headerOptions' => ['style' => 'width: 180px;'],
                                'contentOptions' => ['class' => 'text-muted'],
                            ],
                            [
                                'attribute' => 'change_type',
                                'label' => 'Tipo',
                                'format' => 'raw',
                                'headerOptions' => ['style' => 'width: 180px;'],
                                'value' => function ($model) {
                                    return '<span class="badge badge-' . $model->getChangeTypeColor() . '">'
                                         . '<i class="' . $model->getChangeTypeIcon() . ' mr-1"></i>'
                                         . Html::encode($model->getChangeTypeLabel())
                                         . '</span>';
                                },
                            ],
                            [
                                'attribute' => 'field_name',
                                'label' => 'Campo',
                                'headerOptions' => ['style' => 'width: 150px;'],
                                'value' => function ($model) {
                                    return $model->getFieldLabel();
                                },
                            ],
                            [
                                'attribute' => 'old_value',
                                'label' => 'Valor Anterior',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->change_type === 'password') {
                                        return '<span class="text-muted">********</span>';
                                    }
                                    if ($model->field_name === 'photo') {
                                        return $model->old_value 
                                            ? '<span class="text-muted">(foto anterior)</span>'
                                            : '<span class="text-muted">(sem foto)</span>';
                                    }
                                    return $model->old_value 
                                        ? Html::encode($model->old_value) 
                                        : '<span class="text-muted">(vazio)</span>';
                                },
                            ],
                            [
                                'attribute' => 'new_value',
                                'label' => 'Novo Valor',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if ($model->change_type === 'password') {
                                        return '<span class="text-muted">********</span>';
                                    }
                                    if ($model->field_name === 'photo') {
                                        return $model->new_value 
                                            ? '<span class="text-success">(nova foto)</span>'
                                            : '<span class="text-danger">(foto removida)</span>';
                                    }
                                    return $model->new_value 
                                        ? Html::encode($model->new_value) 
                                        : '<span class="text-muted">(vazio)</span>';
                                },
                            ],
                            [
                                'attribute' => 'ip_address',
                                'label' => 'IP',
                                'headerOptions' => ['style' => 'width: 130px;'],
                                'contentOptions' => ['class' => 'text-muted small'],
                                'value' => function ($model) {
                                    return $model->ip_address ?: '-';
                                },
                            ],
                        ],
                        'pager' => [
                            'options' => ['class' => 'pagination pagination-sm m-0 float-right'],
                            'linkContainerOptions' => ['class' => 'page-item'],
                            'linkOptions' => ['class' => 'page-link'],
                            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                        ],
                    ]); ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-history fa-4x mb-3" style="opacity: 0.3;"></i>
                        <h5>Nenhuma alteração registada</h5>
                        <p>O histórico de alterações aparecerá aqui quando fizer mudanças no seu perfil.</p>
                    </div>
                <?php endif; ?>
                
                <?php Pjax::end(); ?>
            </div>
        </div>

        <!-- Legenda -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>Legenda
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <span class="badge badge-info mr-2">
                            <i class="fas fa-edit mr-1"></i>Atualização de Dados
                        </span>
                        <small class="text-muted">Alteração de informações pessoais</small>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-warning mr-2">
                            <i class="fas fa-key mr-1"></i>Alteração de Palavra-passe
                        </span>
                        <small class="text-muted">Mudança de credenciais de acesso</small>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-success mr-2">
                            <i class="fas fa-camera mr-1"></i>Atualização de Foto
                        </span>
                        <small class="text-muted">Upload ou remoção de foto de perfil</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}
</style>
