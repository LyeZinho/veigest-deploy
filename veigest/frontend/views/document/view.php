<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Document $model */

$this->title = $model->file ? $model->file->original_name : 'Documento #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Gestão Documental', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-file-alt mr-2"></i>Detalhes do Documento
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Voltar', ['index'], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <!-- Card principal com detalhes -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <?php if ($model->file): ?>
                                <i class="fas <?= $model->file->getFileIcon() ?> mr-2"></i>
                            <?php else: ?>
                                <i class="fas fa-file mr-2"></i>
                            <?php endif; ?>
                            <?= Html::encode($this->title) ?>
                        </h3>
                        <div class="card-tools">
                            <span class="badge <?= $model->getStatusBadgeClass() ?>">
                                <?= $model->getStatusDisplayLabel() ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?= DetailView::widget([
                            'model' => $model,
                            'options' => ['class' => 'table table-striped table-bordered detail-view'],
                            'attributes' => [
                                [
                                    'attribute' => 'id',
                                    'label' => 'ID do Documento',
                                ],
                                [
                                    'attribute' => 'type',
                                    'label' => 'Tipo',
                                    'value' => $model->getTypeLabel(),
                                ],
                                [
                                    'label' => 'Ficheiro',
                                    'format' => 'raw',
                                    'value' => function () use ($model) {
                                        if (!$model->file) {
                                            return '<span class="text-muted">Sem ficheiro</span>';
                                        }
                                        return '<i class="fas ' . $model->file->getFileIcon() . ' mr-2"></i>' . 
                                               Html::encode($model->file->original_name) . 
                                               ' <span class="badge badge-secondary">' . $model->file->getFormattedSize() . '</span>';
                                    },
                                ],
                                [
                                    'label' => 'Veículo',
                                    'format' => 'raw',
                                    'value' => function () use ($model) {
                                        if (!$model->vehicle) {
                                            return '<span class="text-muted">Não associado</span>';
                                        }
                                        return '<i class="fas fa-car mr-1"></i>' . 
                                               Html::encode($model->vehicle->license_plate) . ' - ' .
                                               Html::encode($model->vehicle->brand . ' ' . $model->vehicle->model);
                                    },
                                ],
                                [
                                    'label' => 'Motorista',
                                    'format' => 'raw',
                                    'value' => function () use ($model) {
                                        if (!$model->driver) {
                                            return '<span class="text-muted">Não associado</span>';
                                        }
                                        return '<i class="fas fa-user mr-1"></i>' . Html::encode($model->driver->name);
                                    },
                                ],
                                [
                                    'attribute' => 'expiry_date',
                                    'label' => 'Data de Validade',
                                    'format' => 'raw',
                                    'value' => function () use ($model) {
                                        if (empty($model->expiry_date)) {
                                            return '<span class="text-muted">Sem validade definida</span>';
                                        }
                                        
                                        $days = $model->getDaysUntilExpiry();
                                        $formattedDate = Yii::$app->formatter->asDate($model->expiry_date, 'dd/MM/yyyy');
                                        
                                        if ($days > 30) {
                                            return '<span class="text-success">' . $formattedDate . '</span> <small class="text-muted">(' . $days . ' dias restantes)</small>';
                                        } elseif ($days > 0) {
                                            return '<span class="text-warning">' . $formattedDate . '</span> <small class="text-warning">(' . $days . ' dias restantes)</small>';
                                        } elseif ($days === 0) {
                                            return '<span class="text-danger">' . $formattedDate . '</span> <small class="text-danger">(Expira hoje!)</small>';
                                        } else {
                                            return '<span class="text-danger">' . $formattedDate . '</span> <small class="text-danger">(Expirado há ' . abs($days) . ' dias)</small>';
                                        }
                                    },
                                ],
                                [
                                    'attribute' => 'status',
                                    'label' => 'Estado',
                                    'format' => 'raw',
                                    'value' => '<span class="badge ' . $model->getStatusBadgeClass() . '">' . $model->getStatusDisplayLabel() . '</span>',
                                ],
                                [
                                    'attribute' => 'notes',
                                    'label' => 'Observações',
                                    'format' => 'ntext',
                                    'value' => $model->notes ?: 'Sem observações',
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'label' => 'Criado em',
                                    'format' => ['datetime', 'php:d/m/Y H:i:s'],
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'label' => 'Última atualização',
                                    'format' => 'raw',
                                    'value' => $model->updated_at 
                                        ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d/m/Y H:i:s')
                                        : '<span class="text-muted">Nunca atualizado</span>',
                                ],
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Ações -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cogs mr-2"></i>Ações
                        </h3>
                    </div>
                    <div class="card-body">
                        <?= Html::a('<i class="fas fa-download mr-2"></i>Descarregar Ficheiro', 
                            ['download', 'id' => $model->id], 
                            ['class' => 'btn btn-info btn-block mb-2']
                        ) ?>
                        
                        <?= Html::a('<i class="fas fa-edit mr-2"></i>Editar Documento', 
                            ['update', 'id' => $model->id], 
                            ['class' => 'btn btn-warning btn-block mb-2']
                        ) ?>
                        
                        <?= Html::a('<i class="fas fa-trash mr-2"></i>Eliminar Documento', 
                            ['delete', 'id' => $model->id], 
                            [
                                'class' => 'btn btn-danger btn-block',
                                'data' => [
                                    'confirm' => 'Tem a certeza que deseja eliminar este documento? Esta ação não pode ser desfeita.',
                                    'method' => 'post',
                                ],
                            ]
                        ) ?>
                    </div>
                </div>

                <!-- Preview do ficheiro (se for imagem) -->
                <?php if ($model->file && in_array($model->file->getExtension(), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-image mr-2"></i>Pré-visualização
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <img src="<?= $model->file->getDownloadUrl() ?>" 
                             alt="<?= Html::encode($model->file->original_name) ?>" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 300px;">
                    </div>
                </div>
                <?php endif; ?>

                <!-- Informação do ficheiro -->
                <?php if ($model->file): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-alt mr-2"></i>Informação do Ficheiro
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong><i class="fas fa-file mr-2 text-primary"></i>Nome:</strong>
                                <br>
                                <small class="text-muted"><?= Html::encode($model->file->original_name) ?></small>
                            </li>
                            <li class="mb-2">
                                <strong><i class="fas fa-weight mr-2 text-primary"></i>Tamanho:</strong>
                                <br>
                                <small class="text-muted"><?= $model->file->getFormattedSize() ?></small>
                            </li>
                            <li class="mb-2">
                                <strong><i class="fas fa-file-code mr-2 text-primary"></i>Extensão:</strong>
                                <br>
                                <small class="text-muted"><?= strtoupper($model->file->getExtension()) ?></small>
                            </li>
                            <li>
                                <strong><i class="fas fa-calendar mr-2 text-primary"></i>Upload:</strong>
                                <br>
                                <small class="text-muted"><?= Yii::$app->formatter->asDatetime($model->file->created_at, 'dd/MM/yyyy HH:mm') ?></small>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
