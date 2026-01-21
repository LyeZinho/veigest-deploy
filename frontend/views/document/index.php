<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Document;
use frontend\models\DocumentSearch;

/** @var yii\web\View $this */
/** @var frontend\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */

$this->title = 'Gestão Documental';
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-folder-open mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-upload mr-2"></i>Upload Documento', ['create'], [
                    'class' => 'btn btn-primary'
                ]) ?>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        
        <!-- Mensagens Flash -->
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <?= Yii::$app->session->getFlash('success') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= Yii::$app->session->getFlash('error') ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="small-box" style="background-color: var(--primary-color); color: white;">
                    <div class="inner">
                        <h3><?= $stats['total'] ?></h3>
                        <p>Total de Documentos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $stats['valid'] ?></h3>
                        <p>Válidos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $stats['expiring_soon'] ?></h3>
                        <p>Próximos do Vencimento</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $stats['expired'] ?></h3>
                        <p>Expirados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <?php $form = \yii\widgets\ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'options' => ['class' => 'form-inline'],
                ]); ?>
                <div class="row w-100">
                    <div class="col-md-3">
                        <?= Html::textInput('DocumentSearch[searchText]', $searchModel->searchText, [
                            'class' => 'form-control w-100',
                            'placeholder' => 'Procurar documentos...',
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::dropDownList('DocumentSearch[type]', $searchModel->type, 
                            array_merge(['' => 'Todos os Tipos'], Document::getTypesList()), 
                            ['class' => 'form-control w-100']
                        ) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::dropDownList('DocumentSearch[statusFilter]', $searchModel->statusFilter, 
                            DocumentSearch::getStatusFilterOptions(), 
                            ['class' => 'form-control w-100']
                        ) ?>
                    </div>
                    <div class="col-md-3">
                        <?= Html::submitButton('<i class="fas fa-filter mr-2"></i>Filtrar', [
                            'class' => 'btn btn-secondary btn-block'
                        ]) ?>
                    </div>
                </div>
                <?php \yii\widgets\ActiveForm::end(); ?>
            </div>
        </div>

        <!-- Tabela de Documentos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Documentos do Sistema
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary"><?= $dataProvider->getTotalCount() ?> Documentos</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php Pjax::begin(['id' => 'documents-pjax']); ?>
                
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-striped table-hover mb-0'],
                    'layout' => "{items}\n{pager}",
                    'pager' => [
                        'options' => ['class' => 'pagination justify-content-center mt-3 mb-3'],
                        'linkContainerOptions' => ['class' => 'page-item'],
                        'linkOptions' => ['class' => 'page-link'],
                        'disabledListItemSubTagOptions' => ['class' => 'page-link'],
                    ],
                    'columns' => [
                        [
                            'attribute' => 'file.original_name',
                            'label' => 'Documento',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $icon = $model->file ? $model->file->getFileIcon() : 'fa-file';
                                $name = $model->file ? $model->file->original_name : 'Sem ficheiro';
                                return '<i class="fas ' . $icon . ' mr-2"></i>' . Html::encode($name);
                            },
                        ],
                        [
                            'attribute' => 'type',
                            'label' => 'Tipo',
                            'value' => function ($model) {
                                return $model->getTypeLabel();
                            },
                        ],
                        [
                            'label' => 'Veículo/Pessoa',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->vehicle) {
                                    return '<i class="fas fa-car mr-1"></i>' . Html::encode($model->vehicle->license_plate);
                                } elseif ($model->driver) {
                                    return '<i class="fas fa-user mr-1"></i>' . Html::encode($model->driver->name);
                                }
                                return '<span class="text-muted">-</span>';
                            },
                        ],
                        [
                            'attribute' => 'expiry_date',
                            'label' => 'Válido até',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if (empty($model->expiry_date)) {
                                    return '<span class="text-muted">Sem validade</span>';
                                }
                                return Yii::$app->formatter->asDate($model->expiry_date, 'dd/MM/yyyy');
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'label' => 'Estado',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $badgeClass = $model->getStatusBadgeClass();
                                $label = $model->getStatusDisplayLabel();
                                return '<span class="badge ' . $badgeClass . '">' . $label . '</span>';
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'Ações',
                            'headerOptions' => ['style' => 'width: 150px;'],
                            'template' => '{download} {view} {update} {delete}',
                            'buttons' => [
                                'download' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-download"></i>', 
                                        ['download', 'id' => $model->id], 
                                        [
                                            'class' => 'btn btn-sm btn-info mr-1',
                                            'title' => 'Descarregar',
                                            'data-pjax' => '0',
                                        ]
                                    );
                                },
                                'view' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-eye"></i>', 
                                        ['view', 'id' => $model->id], 
                                        [
                                            'class' => 'btn btn-sm btn-secondary mr-1',
                                            'title' => 'Ver detalhes',
                                        ]
                                    );
                                },
                                'update' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-edit"></i>', 
                                        ['update', 'id' => $model->id], 
                                        [
                                            'class' => 'btn btn-sm btn-warning mr-1',
                                            'title' => 'Editar',
                                        ]
                                    );
                                },
                                'delete' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-trash"></i>', 
                                        ['delete', 'id' => $model->id], 
                                        [
                                            'class' => 'btn btn-sm btn-danger',
                                            'title' => 'Eliminar',
                                            'data' => [
                                                'confirm' => 'Tem a certeza que deseja eliminar este documento? Esta ação não pode ser desfeita.',
                                                'method' => 'post',
                                            ],
                                        ]
                                    );
                                },
                            ],
                        ],
                    ],
                    'emptyText' => '<div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum documento encontrado.</p>
                        <a href="' . Url::to(['create']) . '" class="btn btn-primary">
                            <i class="fas fa-upload mr-2"></i>Upload do Primeiro Documento
                        </a>
                    </div>',
                    'emptyTextOptions' => ['class' => 'p-0'],
                ]); ?>
                
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</section>
