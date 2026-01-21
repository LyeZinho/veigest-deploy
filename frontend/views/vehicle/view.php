<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;

/** @var yii\web\View $this */
/** @var frontend\models\Vehicle $model */
/** @var array $costSummary */
/** @var array $recentMaintenances */
/** @var array $recentDocuments */
/** @var array $recentFuelLogs */

$this->title = $model->license_plate . ' - ' . $model->brand . ' ' . $model->model;
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Veículos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->license_plate;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-car mr-2"></i><?= Html::encode($model->license_plate) ?>
                        <?php
                        $statusBadges = [
                            'ativo' => '<span class="badge badge-success ml-2">Ativo</span>',
                            'inativo' => '<span class="badge badge-secondary ml-2">Inativo</span>',
                            'manutencao' => '<span class="badge badge-warning ml-2">Manutenção</span>',
                        ];
                        echo $statusBadges[$model->status] ?? '';
                        ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Veículos</a></li>
                        <li class="breadcrumb-item active"><?= Html::encode($model->license_plate) ?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Botões de Ação -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <?= Html::a('<i class="fas fa-arrow-left"></i> Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
                        <?php if (Yii::$app->user->can('vehicles.update')): ?>
                            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('vehicles.assign')): ?>
                            <?= Html::a('<i class="fas fa-user-plus"></i> Atribuir Condutor', ['assign', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
                        <?php endif; ?>
                        <?= Html::a('<i class="fas fa-history"></i> Histórico Completo', ['history', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('<i class="fas fa-file-alt"></i> Documentos', ['documents', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
                        <?php if (Yii::$app->user->can('vehicles.delete')): ?>
                            <?= Html::a('<i class="fas fa-trash"></i> Apagar', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Tem certeza que deseja apagar este veículo? Esta ação não pode ser desfeita.',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Detalhes do Veículo -->
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informações Técnicas</h3>
                        </div>
                        <div class="card-body">
                            <?= DetailView::widget([
                                'model' => $model,
                                'options' => ['class' => 'table table-striped table-bordered detail-view'],
                                'attributes' => [
                                    [
                                        'attribute' => 'license_plate',
                                        'label' => 'Matrícula',
                                        'value' => '<strong>' . Html::encode($model->license_plate) . '</strong>',
                                        'format' => 'html',
                                    ],
                                    [
                                        'label' => 'Veículo',
                                        'value' => Html::encode($model->brand . ' ' . $model->model . ' (' . $model->year . ')'),
                                    ],
                                    [
                                        'attribute' => 'fuel_type',
                                        'label' => 'Combustível',
                                        'value' => function($model) {
                                            $fuelTypes = [
                                                'gasolina' => '<i class="fas fa-gas-pump text-danger"></i> Gasolina',
                                                'diesel' => '<i class="fas fa-gas-pump text-warning"></i> Diesel',
                                                'eletrico' => '<i class="fas fa-bolt text-success"></i> Elétrico',
                                                'hibrido' => '<i class="fas fa-leaf text-info"></i> Híbrido',
                                                'gpl' => '<i class="fas fa-fire text-primary"></i> GPL',
                                            ];
                                            return $fuelTypes[$model->fuel_type] ?? $model->fuel_type;
                                        },
                                        'format' => 'html',
                                    ],
                                    [
                                        'attribute' => 'mileage',
                                        'label' => 'Quilometragem',
                                        'value' => number_format($model->mileage, 0, ',', '.') . ' km',
                                    ],
                                    [
                                        'attribute' => 'status',
                                        'label' => 'Estado',
                                        'value' => function($model) {
                                            $statuses = [
                                                'ativo' => '<span class="badge badge-success"><i class="fas fa-check"></i> Ativo</span>',
                                                'inativo' => '<span class="badge badge-secondary"><i class="fas fa-pause"></i> Inativo</span>',
                                                'manutencao' => '<span class="badge badge-warning"><i class="fas fa-wrench"></i> Manutenção</span>',
                                            ];
                                            return $statuses[$model->status] ?? $model->status;
                                        },
                                        'format' => 'html',
                                    ],
                                    [
                                        'attribute' => 'created_at',
                                        'label' => 'Registado em',
                                        'value' => Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i'),
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Condutor Atribuído -->
                <div class="col-md-6">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user mr-2"></i>Condutor Atribuído</h3>
                        </div>
                        <div class="card-body">
                            <?php if ($model->driver): ?>
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <?php
                                        // Usa photo ao invés de avatar (campo correto na tabela users)
                                        if ($model->driver->photo):
                                            // Se for URL completa, usa diretamente
                                            $photoSrc = (strpos($model->driver->photo, 'http') === 0) 
                                                ? $model->driver->photo 
                                                : $model->driver->photo;
                                        ?>
                                            <img src="<?= Html::encode($photoSrc) ?>" 
                                                 class="img-circle elevation-2" 
                                                 alt="Foto do Condutor" 
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="img-circle elevation-2 bg-primary d-flex align-items-center justify-content-center" 
                                                 style="width: 80px; height: 80px;">
                                                <i class="fas fa-user fa-2x text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h5 class="mb-1"><?= Html::encode($model->driver->name ?? $model->driver->username) ?></h5>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-envelope mr-1"></i><?= Html::encode($model->driver->email) ?>
                                        </p>
                                        <?php if ($model->driver->phone): ?>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-phone mr-1"></i><?= Html::encode($model->driver->phone) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Nenhum condutor atribuído</p>
                                    <?php if (Yii::$app->user->can('vehicles.assign')): ?>
                                        <?= Html::a('<i class="fas fa-user-plus"></i> Atribuir Condutor', ['assign', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Resumo de Custos -->
                    <?php if (isset($costSummary)): ?>
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-euro-sign mr-2"></i>Resumo de Custos</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box bg-light mb-2">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-wrench"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Manutenções</span>
                                            <span class="info-box-number"><?= number_format($costSummary['maintenance_cost'], 2, ',', '.') ?> €</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box bg-light mb-2">
                                        <span class="info-box-icon bg-danger"><i class="fas fa-gas-pump"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Combustível</span>
                                            <span class="info-box-number"><?= number_format($costSummary['fuel_cost'], 2, ',', '.') ?> €</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="info-box bg-gradient-primary mb-0">
                                        <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Custo Total</span>
                                            <span class="info-box-number"><?= number_format($costSummary['total_cost'], 2, ',', '.') ?> €</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Histórico Recente -->
            <div class="row">
                <!-- Últimas Manutenções -->
                <div class="col-md-6">
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-wrench mr-2"></i>Últimas Manutenções</h3>
                            <div class="card-tools">
                                <?= Html::a('Ver Todas <i class="fas fa-arrow-right"></i>', ['history', 'id' => $model->id, 'type' => 'maintenance'], ['class' => 'btn btn-tool']) ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentMaintenances)): ?>
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Data</th>
                                            <th>Custo</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMaintenances as $maintenance): ?>
                                            <tr>
                                                <td><?= Html::encode($maintenance->type ?? $maintenance->description) ?></td>
                                                <td><?= Yii::$app->formatter->asDate($maintenance->date ?? $maintenance->created_at, 'php:d/m/Y') ?></td>
                                                <td><?= number_format($maintenance->cost ?? 0, 2, ',', '.') ?> €</td>
                                                <td>
                                                    <?php
                                                    $status = $maintenance->status ?? 'concluida';
                                                    $statusLabels = [
                                                        'pendente' => '<span class="badge badge-warning">Pendente</span>',
                                                        'em_curso' => '<span class="badge badge-info">Em Curso</span>',
                                                        'concluida' => '<span class="badge badge-success">Concluída</span>',
                                                    ];
                                                    echo $statusLabels[$status] ?? '<span class="badge badge-secondary">' . $status . '</span>';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <p class="text-muted mb-0">Sem manutenções registadas</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Últimos Abastecimentos -->
                <div class="col-md-6">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-gas-pump mr-2"></i>Últimos Abastecimentos</h3>
                            <div class="card-tools">
                                <?= Html::a('Ver Todos <i class="fas fa-arrow-right"></i>', ['history', 'id' => $model->id, 'type' => 'fuel'], ['class' => 'btn btn-tool']) ?>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($recentFuelLogs)): ?>
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Litros</th>
                                            <th>Custo</th>
                                            <th>Km</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentFuelLogs as $fuel): ?>
                                            <tr>
                                                <td><?= Yii::$app->formatter->asDate($fuel->date ?? $fuel->created_at, 'php:d/m/Y') ?></td>
                                                <td><?= number_format($fuel->liters, 2, ',', '.') ?> L</td>
                                                <td><?= number_format($fuel->total_cost, 2, ',', '.') ?> €</td>
                                                <td><?= number_format($fuel->mileage, 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-gas-pump fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Sem abastecimentos registados</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documentos Recentes -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Documentos Recentes</h3>
                            <div class="card-tools">
                                <?= Html::a('Ver Todos <i class="fas fa-arrow-right"></i>', ['documents', 'id' => $model->id], ['class' => 'btn btn-tool']) ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recentDocuments)): ?>
                                <div class="row">
                                    <?php foreach ($recentDocuments as $document): ?>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="card card-outline card-primary">
                                                <div class="card-body text-center">
                                                    <?php
                                                    $extension = pathinfo($document->file_path ?? '', PATHINFO_EXTENSION);
                                                    $icons = [
                                                        'pdf' => 'fas fa-file-pdf text-danger',
                                                        'doc' => 'fas fa-file-word text-primary',
                                                        'docx' => 'fas fa-file-word text-primary',
                                                        'jpg' => 'fas fa-file-image text-success',
                                                        'jpeg' => 'fas fa-file-image text-success',
                                                        'png' => 'fas fa-file-image text-success',
                                                    ];
                                                    $iconClass = $icons[strtolower($extension)] ?? 'fas fa-file text-secondary';
                                                    ?>
                                                    <i class="<?= $iconClass ?> fa-3x mb-2"></i>
                                                    <h6 class="mb-1"><?= Html::encode($document->name ?? $document->title ?? 'Documento') ?></h6>
                                                    <small class="text-muted"><?= Html::encode($document->type ?? 'Outro') ?></small>
                                                    <?php if ($document->expiry_date): ?>
                                                        <br>
                                                        <?php
                                                        $expiryDate = strtotime($document->expiry_date);
                                                        $daysUntilExpiry = ceil(($expiryDate - time()) / 86400);
                                                        if ($daysUntilExpiry < 0):
                                                        ?>
                                                            <span class="badge badge-danger">Expirado</span>
                                                        <?php elseif ($daysUntilExpiry <= 30): ?>
                                                            <span class="badge badge-warning">Expira em <?= $daysUntilExpiry ?> dias</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-success">Válido até <?= date('d/m/Y', $expiryDate) ?></span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Nenhum documento registado</p>
                                    <?= Html::a('<i class="fas fa-plus"></i> Adicionar Documento', ['documents', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
