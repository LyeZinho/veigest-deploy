<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use common\models\SupportTicket;

/** @var yii\web\View $this */
/** @var common\models\SupportTicket $model */

$this->title = 'Ticket #' . $model->id . ': ' . $model->subject;
$this->params['breadcrumbs'][] = ['label' => 'Tickets de Suporte', 'url' => ['index']];
$this->params['breadcrumbs'][] = '#' . $model->id;

$priorityColors = [
    'low' => 'secondary',
    'medium' => 'info',
    'high' => 'warning',
    'urgent' => 'danger',
];

$statusColors = [
    'open' => 'success',
    'in_progress' => 'info',
    'waiting_response' => 'warning',
    'resolved' => 'purple',
    'closed' => 'secondary',
];
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-ticket-alt mr-2"></i>Ticket #<?= $model->id ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-1"></i>Voltar', ['index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Ticket Details -->
            <div class="col-md-8">
                <!-- Main Ticket Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="badge badge-<?= $priorityColors[$model->priority] ?? 'secondary' ?> mr-2">
                                <?= $model->getPriorityLabel() ?>
                            </span>
                            <?= Html::encode($model->subject) ?>
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-<?= $statusColors[$model->status] ?? 'secondary' ?>">
                                <?= $model->getStatusLabel() ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5 class="text-muted mb-3">Descrição do Problema</h5>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(Html::encode($model->body)) ?>
                            </div>
                        </div>
                        
                        <?php if ($model->admin_response): ?>
                        <div class="mt-4">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-reply mr-1"></i>Resposta da Equipa
                            </h5>
                            <div class="p-3 bg-info text-white rounded">
                                <?= nl2br(Html::encode($model->admin_response)) ?>
                            </div>
                            <?php if ($model->responder): ?>
                            <small class="text-muted">
                                Respondido por <?= Html::encode($model->responder->name ?? $model->responder->username) ?>
                                em <?= Yii::$app->formatter->asDatetime($model->responded_at, 'php:d/m/Y H:i') ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Response Form -->
                <?php if ($model->canRespond()): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title"><i class="fas fa-reply mr-2"></i>Responder ao Ticket</h3>
                    </div>
                    <div class="card-body">
                        <?= Html::beginForm(['respond', 'id' => $model->id], 'post') ?>
                        <div class="form-group">
                            <label for="response">Resposta</label>
                            <textarea name="response" id="response" class="form-control" rows="5" 
                                      placeholder="Escreva a sua resposta ao utilizador..." required><?= Html::encode($model->admin_response) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i>Enviar Resposta
                        </button>
                        <?= Html::endForm() ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Ticket Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Informações</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tr>
                                <th style="width: 40%">ID</th>
                                <td>#<?= $model->id ?></td>
                            </tr>
                            <tr>
                                <th>Estado</th>
                                <td>
                                    <span class="badge badge-<?= $statusColors[$model->status] ?? 'secondary' ?>">
                                        <?= $model->getStatusLabel() ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Prioridade</th>
                                <td>
                                    <span class="badge badge-<?= $priorityColors[$model->priority] ?? 'secondary' ?>">
                                        <?= $model->getPriorityLabel() ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Categoria</th>
                                <td><?= $model->getCategoryLabel() ?></td>
                            </tr>
                            <tr>
                                <th>Criado Em</th>
                                <td><?= Yii::$app->formatter->asDatetime($model->created_at, 'php:d/m/Y H:i') ?></td>
                            </tr>
                            <?php if ($model->responded_at): ?>
                            <tr>
                                <th>Respondido Em</th>
                                <td><?= Yii::$app->formatter->asDatetime($model->responded_at, 'php:d/m/Y H:i') ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- User Info -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user mr-2"></i>Utilizador</h3>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>Nome:</strong><br>
                            <?= Html::encode($model->name) ?>
                            <?php if ($model->user_id): ?>
                            <span class="badge badge-success ml-2">Registado</span>
                            <?php else: ?>
                            <span class="badge badge-secondary ml-2">Visitante</span>
                            <?php endif; ?>
                        </p>
                        <p>
                            <strong>Email:</strong><br>
                            <a href="mailto:<?= Html::encode($model->email) ?>"><?= Html::encode($model->email) ?></a>
                        </p>
                        <?php if ($model->user): ?>
                        <p>
                            <strong>Função:</strong><br>
                            <?= Html::encode($model->user->roles ?? 'N/A') ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Change -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Alterar Estado</h3>
                    </div>
                    <div class="card-body">
                        <?= Html::beginForm(['update-status', 'id' => $model->id], 'post') ?>
                        <div class="form-group">
                            <select name="status" class="form-control">
                                <?php foreach (SupportTicket::getStatusOptions() as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $model->status === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fas fa-save mr-1"></i>Atualizar Estado
                        </button>
                        <?= Html::endForm() ?>
                    </div>
                </div>

                <!-- Actions -->
                <?php if (Yii::$app->user->can('admin')): ?>
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Zona Perigosa</h3>
                    </div>
                    <div class="card-body">
                        <?= Html::a('<i class="fas fa-trash mr-1"></i>Eliminar Ticket', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-block',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja eliminar este ticket? Esta ação não pode ser revertida.',
                                'method' => 'post',
                            ],
                        ]) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
.badge-purple {
    background-color: #6f42c1;
    color: white;
}
</style>
