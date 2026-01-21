<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var yii\data\ActiveDataProvider $historyProvider */

$this->title = 'Meu Perfil';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

// Determina a URL da foto de perfil
$photoUrl = $user->photo 
    ? $user->photo 
    : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=150&background=09BC8A&color=fff';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-user-circle mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-edit mr-2"></i>Editar Perfil', ['update'], [
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
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle mr-2"></i>
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Coluna Principal - Dados do Perfil -->
            <div class="col-lg-8">
                <!-- Card de Informações Pessoais -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-id-card mr-2"></i>Informações Pessoais
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div class="profile-photo-container mb-3">
                                    <img src="<?= Html::encode($photoUrl) ?>" 
                                         alt="Foto de Perfil" 
                                         class="img-fluid rounded-circle profile-photo"
                                         style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #09BC8A;">
                                </div>
                                <?php if ($user->photo): ?>
                                    <?= Html::a('<i class="fas fa-trash mr-1"></i>Remover Foto', ['delete-photo'], [
                                        'class' => 'btn btn-outline-danger btn-sm',
                                        'data' => [
                                            'confirm' => 'Tem certeza que deseja remover a foto de perfil?',
                                            'method' => 'post',
                                        ],
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless">
                                    <tr>
                                        <th style="width: 35%;"><i class="fas fa-user text-muted mr-2"></i>Nome:</th>
                                        <td><?= Html::encode($user->name) ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-at text-muted mr-2"></i>Username:</th>
                                        <td><?= Html::encode($user->username) ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-envelope text-muted mr-2"></i>Email:</th>
                                        <td><?= Html::encode($user->email) ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-phone text-muted mr-2"></i>Telefone:</th>
                                        <td><?= $user->phone ? Html::encode($user->phone) : '<span class="text-muted">Não definido</span>' ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-user-tag text-muted mr-2"></i>Função:</th>
                                        <td>
                                            <?php
                                            $role = $user->getRole();
                                            $roleLabels = [
                                                'admin' => ['label' => 'Administrador', 'class' => 'badge-danger'],
                                                'manager' => ['label' => 'Manager', 'class' => 'badge-primary'],
                                                'driver' => ['label' => 'Driver', 'class' => 'badge-success'],
                                            ];
                                            $roleInfo = $roleLabels[$role] ?? ['label' => ucfirst($role ?? 'N/A'), 'class' => 'badge-secondary'];
                                            ?>
                                            <span class="badge <?= $roleInfo['class'] ?>"><?= $roleInfo['label'] ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-toggle-on text-muted mr-2"></i>Status:</th>
                                        <td>
                                            <?php if ($user->status === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger"><?= ucfirst($user->status) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Driver Information Card (if applicable) -->
                <?php if ($user->license_number || $user->hasRole('driver')): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-id-card-alt mr-2"></i>Driver Data
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-credit-card text-muted mr-2"></i>Carta de Condução:</strong></p>
                                <p class="ml-4"><?= $user->license_number ? Html::encode($user->license_number) : '<span class="text-muted">Não definido</span>' ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-calendar-alt text-muted mr-2"></i>Validade:</strong></p>
                                <p class="ml-4">
                                    <?php if ($user->license_expiry): ?>
                                        <?php 
                                        $expiryDate = new DateTime($user->license_expiry);
                                        $today = new DateTime();
                                        $diff = $today->diff($expiryDate);
                                        $isExpired = $expiryDate < $today;
                                        $isExpiringSoon = !$isExpired && $diff->days <= 30;
                                        ?>
                                        <span class="<?= $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : '') ?>">
                                            <?= Yii::$app->formatter->asDate($user->license_expiry, 'long') ?>
                                            <?php if ($isExpired): ?>
                                                <span class="badge badge-danger ml-2">Expirada</span>
                                            <?php elseif ($isExpiringSoon): ?>
                                                <span class="badge badge-warning ml-2">Expira em <?= $diff->days ?> dias</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Não definida</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Card de Histórico de Alterações -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-history mr-2"></i>Histórico de Alterações Recentes
                        </h3>
                        <?= Html::a('Ver Tudo', ['history'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($historyProvider->totalCount > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Campo</th>
                                            <th>Detalhes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historyProvider->getModels() as $history): ?>
                                            <tr>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= Yii::$app->formatter->asDatetime($history->created_at, 'short') ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $history->getChangeTypeColor() ?>">
                                                        <i class="<?= $history->getChangeTypeIcon() ?> mr-1"></i>
                                                        <?= $history->getChangeTypeLabel() ?>
                                                    </span>
                                                </td>
                                                <td><?= Html::encode($history->getFieldLabel()) ?></td>
                                                <td>
                                                    <?php if ($history->change_type === 'password'): ?>
                                                        <span class="text-muted">Palavra-passe alterada</span>
                                                    <?php elseif ($history->change_type === 'photo'): ?>
                                                        <span class="text-muted">Foto <?= $history->new_value ? 'atualizada' : 'removida' ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= Html::encode($history->old_value ?: '(vazio)') ?></span>
                                                        <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                                        <span><?= Html::encode($history->new_value ?: '(vazio)') ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-history fa-3x mb-3" style="opacity: 0.3;"></i>
                                <p>Nenhuma alteração registada.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral - Ações e Info Adicional -->
            <div class="col-lg-4">
                <!-- Card de Ações Rápidas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt mr-2"></i>Ações Rápidas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?= Html::a('<i class="fas fa-edit mr-2"></i>Editar Informações', ['update'], [
                                'class' => 'btn btn-outline-primary btn-block mb-2'
                            ]) ?>
                            <?= Html::a('<i class="fas fa-key mr-2"></i>Alterar Palavra-passe', ['change-password'], [
                                'class' => 'btn btn-outline-warning btn-block mb-2'
                            ]) ?>
                            <?= Html::a('<i class="fas fa-history mr-2"></i>Ver Histórico Completo', ['history'], [
                                'class' => 'btn btn-outline-info btn-block'
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Card de Informações da Conta -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>Informações da Conta
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <small class="text-muted d-block">Conta criada em</small>
                                <strong><?= Yii::$app->formatter->asDatetime(strtotime($user->created_at), 'php:d/m/Y \à\s H:i') ?></strong>
                            </li>
                            <li class="mb-3">
                                <small class="text-muted d-block">Última atualização</small>
                                <strong><?= $user->updated_at ? Yii::$app->formatter->asDatetime(strtotime($user->updated_at), 'php:d/m/Y \à\s H:i') : 'Nunca' ?></strong>
                            </li>
                            <li>
                                <small class="text-muted d-block">ID do Utilizador</small>
                                <strong>#<?= $user->id ?></strong>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Card de Segurança -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-shield-alt mr-2"></i>Segurança
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Mantenha sua conta segura alterando sua palavra-passe regularmente.
                        </p>
                        <ul class="small text-muted mb-0">
                            <li>Use pelo menos 6 caracteres</li>
                            <li>Inclua letras maiúsculas e minúsculas</li>
                            <li>Adicione números</li>
                            <li>Não compartilhe sua senha</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.profile-photo {
    transition: transform 0.3s ease;
}
.profile-photo:hover {
    transform: scale(1.05);
}
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.table th {
    font-weight: 600;
    color: #495057;
}
</style>
