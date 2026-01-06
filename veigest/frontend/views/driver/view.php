<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use frontend\models\Driver;

/** @var yii\web\View $this */
/** @var frontend\models\Driver $model */
/** @var yii\data\ActiveDataProvider $vehiclesProvider */
/** @var yii\data\ActiveDataProvider $routesProvider */
/** @var array $stats */

$this->title = $model->getDisplayName();
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Condutores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                        <i class="fas fa-user mr-2"></i><?= Html::encode($this->title) ?>
                        <?php
                        $statusBadges = [
                            Driver::STATUS_ACTIVE => '<span class="badge badge-success ml-2">Ativo</span>',
                            Driver::STATUS_INACTIVE => '<span class="badge badge-secondary ml-2">Inativo</span>',
                        ];
                        echo $statusBadges[$model->status] ?? '';
                        ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['dashboard/index']) ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= \yii\helpers\Url::to(['index']) ?>">Condutores</a></li>
                        <li class="breadcrumb-item active"><?= Html::encode($model->getDisplayName()) ?></li>
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
                        <?php if (Yii::$app->user->can('drivers.update')): ?>
                            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
                        <?php endif; ?>
                        <?php if (Yii::$app->user->can('drivers.delete')): ?>
                            <?= Html::a('<i class="fas fa-trash"></i> Apagar', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-danger',
                                'data' => [
                                    'confirm' => 'Tem certeza que deseja apagar este condutor? Esta ação não pode ser desfeita.',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Informações do Condutor -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <?php
                                $avatarUrl = $model->getAvatarUrl();
                                if ($avatarUrl):
                                ?>
                                    <img class="profile-user-img img-fluid img-circle" 
                                         src="<?= $avatarUrl ?>" 
                                         alt="Avatar"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="profile-user-img img-fluid img-circle bg-primary d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 100px; height: 100px;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h3 class="profile-username text-center"><?= Html::encode($model->getDisplayName()) ?></h3>
                            <p class="text-muted text-center"><?= Html::encode($model->email) ?></p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b><i class="fas fa-phone mr-2"></i>Telefone</b>
                                    <a class="float-right"><?= Html::encode($model->phone ?: '-') ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-id-card mr-2"></i>Carta</b>
                                    <a class="float-right"><?= Html::encode($model->license_number ?: '-') ?></a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-calendar mr-2"></i>Validade</b>
                                    <a class="float-right">
                                        <?php if ($model->license_expiry): ?>
                                            <?= Yii::$app->formatter->asDate($model->license_expiry, 'php:d/m/Y') ?>
                                            <?php
                                            $licenseValid = $model->isLicenseValid();
                                            $days = $model->getDaysUntilLicenseExpiry();
                                            if ($licenseValid === false):
                                            ?>
                                                <span class="badge badge-danger">Expirada</span>
                                            <?php elseif ($days !== null && $days <= 30): ?>
                                                <span class="badge badge-warning"><?= $days ?> dias</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-car mr-2"></i>Veículos</b>
                                    <a class="float-right"><span class="badge badge-primary"><?= $stats['total_vehicles'] ?? 0 ?></span></a>
                                </li>
                                <li class="list-group-item">
                                    <b><i class="fas fa-route mr-2"></i>Rotas</b>
                                    <a class="float-right"><span class="badge badge-info"><?= $stats['total_routes'] ?? 0 ?></span></a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <?php if (isset($stats)): ?>
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Estatísticas</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <div class="info-box bg-light mb-2">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Rotas Concluídas</span>
                                            <span class="info-box-number text-success"><?= $stats['completed_routes'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="info-box bg-light mb-2">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Disponível</span>
                                            <span class="info-box-number">
                                                <?php if ($model->isAvailable()): ?>
                                                    <span class="text-success"><i class="fas fa-check-circle"></i> Sim</span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Não</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Veículos e Rotas -->
                <div class="col-md-8">
                    <!-- Veículos Atribuídos -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-car mr-2"></i>Veículos Atribuídos</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (isset($vehiclesProvider) && $vehiclesProvider->getTotalCount() > 0): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Matrícula</th>
                                            <th>Marca/Modelo</th>
                                            <th>Ano</th>
                                            <th>Estado</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vehiclesProvider->getModels() as $vehicle): ?>
                                            <tr>
                                                <td><strong><?= Html::encode($vehicle->license_plate) ?></strong></td>
                                                <td><?= Html::encode($vehicle->brand . ' ' . $vehicle->model) ?></td>
                                                <td><?= Html::encode($vehicle->year) ?></td>
                                                <td>
                                                    <?php
                                                    $vehicleStatuses = [
                                                        'ativo' => '<span class="badge badge-success">Ativo</span>',
                                                        'inativo' => '<span class="badge badge-secondary">Inativo</span>',
                                                        'manutencao' => '<span class="badge badge-warning">Manutenção</span>',
                                                    ];
                                                    echo $vehicleStatuses[$vehicle->status] ?? $vehicle->status;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (Yii::$app->user->can('vehicles.view')): ?>
                                                        <?= Html::a('<i class="fas fa-eye"></i>', ['vehicle/view', 'id' => $vehicle->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-car-side fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Nenhum veículo atribuído a este condutor.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Últimas Rotas -->
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-route mr-2"></i>Últimas Rotas</h3>
                        </div>
                        <div class="card-body p-0">
                            <?php if (isset($routesProvider) && $routesProvider->getTotalCount() > 0): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Origem</th>
                                            <th>Destino</th>
                                            <th>Data</th>
                                            <th>Estado</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($routesProvider->getModels() as $route): ?>
                                            <tr>
                                                <td><?= Html::encode($route->origin ?? '-') ?></td>
                                                <td><?= Html::encode($route->destination ?? '-') ?></td>
                                                <td><?= Yii::$app->formatter->asDate($route->date ?? $route->created_at, 'php:d/m/Y') ?></td>
                                                <td>
                                                    <?php
                                                    $routeStatuses = [
                                                        'pendente' => '<span class="badge badge-warning">Pendente</span>',
                                                        'em_curso' => '<span class="badge badge-info">Em Curso</span>',
                                                        'concluida' => '<span class="badge badge-success">Concluída</span>',
                                                        'cancelada' => '<span class="badge badge-danger">Cancelada</span>',
                                                    ];
                                                    echo $routeStatuses[$route->status ?? 'pendente'] ?? '<span class="badge badge-secondary">' . ($route->status ?? '-') . '</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (Yii::$app->user->can('routes.view')): ?>
                                                        <?= Html::a('<i class="fas fa-eye"></i>', ['route/view', 'id' => $route->id], ['class' => 'btn btn-info btn-sm']) ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Nenhuma rota registada para este condutor.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informações do Sistema</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">ID:</dt>
                                <dd class="col-sm-8"><?= $model->id ?></dd>
                                
                                <dt class="col-sm-4">Username:</dt>
                                <dd class="col-sm-8"><?= Html::encode($model->username ?? '-') ?></dd>
                                
                                <dt class="col-sm-4">Criado em:</dt>
                                <dd class="col-sm-8"><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i:s') ?></dd>
                                
                                <dt class="col-sm-4">Atualizado em:</dt>
                                <dd class="col-sm-8"><?= $model->updated_at ? Yii::$app->formatter->asDatetime($model->updated_at, 'php:d/m/Y H:i:s') : '-' ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
