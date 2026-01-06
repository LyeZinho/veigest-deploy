<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */
/** @var yii\data\ActiveDataProvider $documentProvider */

$this->title = 'Documentos - ' . $model->license_plate;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->license_plate, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Documentos';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-file-alt mr-2"></i>Documentos do Veículo
                    </h1>
                    <small class="text-muted"><?= Html::encode($model->brand . ' ' . $model->model . ' - ' . $model->license_plate) ?></small>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Veículos</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['view', 'id' => $model->id]) ?>"><?= Html::encode($model->license_plate) ?></a></li>
                        <li class="breadcrumb-item active">Documentos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Botões de Navegação -->
            <div class="row mb-3">
                <div class="col-12">
                    <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar ao Veículo', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
                </div>
            </div>

            <!-- Alertas de Documentos Expirados ou Próximos de Expirar -->
            <?php
            $expiredDocs = [];
            $expiringDocs = [];
            foreach ($documentProvider->getModels() as $doc) {
                if ($doc->expiry_date) {
                    $expiryDate = strtotime($doc->expiry_date);
                    $daysUntilExpiry = ceil(($expiryDate - time()) / 86400);
                    if ($daysUntilExpiry < 0) {
                        $expiredDocs[] = $doc;
                    } elseif ($daysUntilExpiry <= 30) {
                        $expiringDocs[] = ['doc' => $doc, 'days' => $daysUntilExpiry];
                    }
                }
            }
            ?>

            <?php if (!empty($expiredDocs)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-ban"></i> Documentos Expirados!</h5>
                    <ul class="mb-0">
                        <?php foreach ($expiredDocs as $doc): ?>
                            <li><strong><?= Html::encode($doc->name ?? $doc->title ?? 'Documento') ?></strong> - Expirou em <?= date('d/m/Y', strtotime($doc->expiry_date)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($expiringDocs)): ?>
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Documentos a Expirar!</h5>
                    <ul class="mb-0">
                        <?php foreach ($expiringDocs as $item): ?>
                            <li><strong><?= Html::encode($item['doc']->name ?? $item['doc']->title ?? 'Documento') ?></strong> - Expira em <?= $item['days'] ?> dias (<?= date('d/m/Y', strtotime($item['doc']->expiry_date)) ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Lista de Documentos -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-folder-open mr-2"></i>Documentos Registados</h3>
                            <div class="card-tools">
                                <span class="badge badge-primary"><?= $documentProvider->getTotalCount() ?> documento(s)</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if ($documentProvider->getTotalCount() > 0): ?>
                                <div class="row">
                                    <?php foreach ($documentProvider->getModels() as $document): ?>
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card card-outline <?php
                                                if ($document->expiry_date) {
                                                    $daysUntilExpiry = ceil((strtotime($document->expiry_date) - time()) / 86400);
                                                    if ($daysUntilExpiry < 0) {
                                                        echo 'card-danger';
                                                    } elseif ($daysUntilExpiry <= 30) {
                                                        echo 'card-warning';
                                                    } else {
                                                        echo 'card-success';
                                                    }
                                                } else {
                                                    echo 'card-secondary';
                                                }
                                            ?>">
                                                <div class="card-body text-center">
                                                    <?php
                                                    $extension = pathinfo($document->file_path ?? '', PATHINFO_EXTENSION);
                                                    $icons = [
                                                        'pdf' => 'fas fa-file-pdf text-danger',
                                                        'doc' => 'fas fa-file-word text-primary',
                                                        'docx' => 'fas fa-file-word text-primary',
                                                        'xls' => 'fas fa-file-excel text-success',
                                                        'xlsx' => 'fas fa-file-excel text-success',
                                                        'jpg' => 'fas fa-file-image text-info',
                                                        'jpeg' => 'fas fa-file-image text-info',
                                                        'png' => 'fas fa-file-image text-info',
                                                        'gif' => 'fas fa-file-image text-info',
                                                    ];
                                                    $iconClass = $icons[strtolower($extension)] ?? 'fas fa-file text-secondary';
                                                    ?>
                                                    <i class="<?= $iconClass ?> fa-3x mb-2"></i>
                                                    <h6 class="card-title mb-1"><?= Html::encode($document->name ?? $document->title ?? 'Documento') ?></h6>
                                                    <p class="text-muted small mb-2">
                                                        <?php
                                                        $types = [
                                                            'seguro' => 'Seguro',
                                                            'inspecao' => 'Inspeção',
                                                            'registro' => 'Registo',
                                                            'licenca' => 'Licença',
                                                            'contrato' => 'Contrato',
                                                            'outro' => 'Outro',
                                                        ];
                                                        echo Html::encode($types[$document->type ?? 'outro'] ?? $document->type ?? 'Outro');
                                                        ?>
                                                    </p>
                                                    
                                                    <?php if ($document->expiry_date): ?>
                                                        <?php
                                                        $expiryDate = strtotime($document->expiry_date);
                                                        $daysUntilExpiry = ceil(($expiryDate - time()) / 86400);
                                                        ?>
                                                        <p class="mb-2">
                                                            <?php if ($daysUntilExpiry < 0): ?>
                                                                <span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Expirado há <?= abs($daysUntilExpiry) ?> dias</span>
                                                            <?php elseif ($daysUntilExpiry <= 7): ?>
                                                                <span class="badge badge-danger"><i class="fas fa-clock"></i> Expira em <?= $daysUntilExpiry ?> dias</span>
                                                            <?php elseif ($daysUntilExpiry <= 30): ?>
                                                                <span class="badge badge-warning"><i class="fas fa-clock"></i> Expira em <?= $daysUntilExpiry ?> dias</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success"><i class="fas fa-check"></i> Válido até <?= date('d/m/Y', $expiryDate) ?></span>
                                                            <?php endif; ?>
                                                        </p>
                                                    <?php else: ?>
                                                        <p class="mb-2">
                                                            <span class="badge badge-secondary">Sem data de expiração</span>
                                                        </p>
                                                    <?php endif; ?>

                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($document->file && $document->file->path && file_exists(Yii::getAlias('@frontend/web' . $document->file->path))): ?>
                                                            <?= Html::a('<i class="fas fa-download"></i>', $document->file->path, [
                                                                'class' => 'btn btn-primary',
                                                                'title' => 'Download',
                                                                'target' => '_blank',
                                                            ]) ?>
                                                            <?= Html::a('<i class="fas fa-eye"></i>', $document->file->path, [
                                                                'class' => 'btn btn-info',
                                                                'title' => 'Visualizar',
                                                                'target' => '_blank',
                                                            ]) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="card-footer text-muted small">
                                                    <i class="fas fa-calendar"></i> Adicionado em <?= Yii::$app->formatter->asDate($document->created_at, 'php:d/m/Y') ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                    <h4>Nenhum documento registado</h4>
                                    <p class="text-muted">Este veículo ainda não possui documentos registados.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar com Estatísticas e Info -->
                <div class="col-md-4">
                    <!-- Estatísticas -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Estatísticas</h3>
                        </div>
                        <div class="card-body">
                            <?php
                            $totalDocs = $documentProvider->getTotalCount();
                            $validDocs = 0;
                            $expiredCount = count($expiredDocs);
                            $expiringCount = count($expiringDocs);
                            
                            foreach ($documentProvider->getModels() as $doc) {
                                if (!$doc->expiry_date) {
                                    $validDocs++;
                                } else {
                                    $daysUntilExpiry = ceil((strtotime($doc->expiry_date) - time()) / 86400);
                                    if ($daysUntilExpiry > 30) {
                                        $validDocs++;
                                    }
                                }
                            }
                            ?>
                            <div class="row">
                                <div class="col-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-success"><i class="fas fa-check"></i></span>
                                        <h5 class="description-header"><?= $validDocs ?></h5>
                                        <span class="description-text">VÁLIDOS</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="description-block">
                                        <span class="description-percentage text-danger"><i class="fas fa-times"></i></span>
                                        <h5 class="description-header"><?= $expiredCount ?></h5>
                                        <span class="description-text">EXPIRADOS</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <div class="description-block border-right">
                                        <span class="description-percentage text-warning"><i class="fas fa-clock"></i></span>
                                        <h5 class="description-header"><?= $expiringCount ?></h5>
                                        <span class="description-text">A EXPIRAR</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="description-block">
                                        <span class="description-percentage text-info"><i class="fas fa-file"></i></span>
                                        <h5 class="description-header"><?= $totalDocs ?></h5>
                                        <span class="description-text">TOTAL</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipos de Documentos Comuns -->
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Tipos de Documentos</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-shield-alt text-primary mr-2"></i>Seguro</span>
                                    <small class="text-muted">Obrigatório</small>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-clipboard-check text-success mr-2"></i>Inspeção</span>
                                    <small class="text-muted">Obrigatório</small>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-id-card text-info mr-2"></i>Registo</span>
                                    <small class="text-muted">Obrigatório</small>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-file-contract text-warning mr-2"></i>Licença</span>
                                    <small class="text-muted">Opcional</small>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-file-signature text-secondary mr-2"></i>Contrato</span>
                                    <small class="text-muted">Opcional</small>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Info do Veículo -->
                    <div class="card card-dark card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-car mr-2"></i>Veículo</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-5">Matrícula:</dt>
                                <dd class="col-7"><?= Html::encode($model->license_plate) ?></dd>
                                <dt class="col-5">Marca/Modelo:</dt>
                                <dd class="col-7"><?= Html::encode($model->brand . ' ' . $model->model) ?></dd>
                                <dt class="col-5">Ano:</dt>
                                <dd class="col-7"><?= Html::encode($model->year) ?></dd>
                                <dt class="col-5">Estado:</dt>
                                <dd class="col-7">
                                    <?php
                                    $statusBadges = [
                                        'ativo' => '<span class="badge badge-success">Ativo</span>',
                                        'inativo' => '<span class="badge badge-secondary">Inativo</span>',
                                        'manutencao' => '<span class="badge badge-warning">Manutenção</span>',
                                    ];
                                    echo $statusBadges[$model->status] ?? $model->status;
                                    ?>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
