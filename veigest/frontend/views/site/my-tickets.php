<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\models\SupportTicket;

/** @var yii\web\View $this */
/** @var common\models\SupportTicket[] $tickets */

$this->title = 'Meus Tickets';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

$priorityColors = [
    'low' => 'secondary',
    'medium' => 'info',
    'high' => 'warning',
    'urgent' => 'danger',
];

$priorityIcons = [
    'low' => 'arrow-down',
    'medium' => 'minus',
    'high' => 'arrow-up',
    'urgent' => 'exclamation-triangle',
];

$statusColors = [
    'open' => 'success',
    'in_progress' => 'primary',
    'waiting_response' => 'warning',
    'resolved' => 'info',
    'closed' => 'secondary',
];

$statusIcons = [
    'open' => 'envelope-open',
    'in_progress' => 'spinner',
    'waiting_response' => 'hourglass-half',
    'resolved' => 'check-circle',
    'closed' => 'times-circle',
];

// Calcular estatísticas
$stats = [
    'open' => ['label' => 'Abertos', 'icon' => 'envelope-open', 'color' => 'success'],
    'in_progress' => ['label' => 'Em Progresso', 'icon' => 'spinner', 'color' => 'primary'],
    'waiting_response' => ['label' => 'Aguardando', 'icon' => 'hourglass-half', 'color' => 'warning'],
    'resolved' => ['label' => 'Resolvidos', 'icon' => 'check-circle', 'color' => 'info'],
    'closed' => ['label' => 'Fechados', 'icon' => 'times-circle', 'color' => 'secondary'],
];
$counts = [];
foreach ($tickets as $t) {
    $counts[$t->status] = ($counts[$t->status] ?? 0) + 1;
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-ticket-alt text-primary"></i> Meus Tickets
                </h1>
            </div>
            <div class="col-sm-6">
                <div class="float-right">
                    <?= Html::a('<i class="fas fa-plus"></i> Novo Ticket', ['ticket'], [
                        'class' => 'btn btn-primary'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">

        <?php if (empty($tickets)): ?>
        <!-- Empty State -->
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-ticket-alt fa-5x text-muted"></i>
                </div>
                <h3 class="text-muted">Nenhum ticket encontrado</h3>
                <p class="text-muted mb-4">Ainda não criou nenhum ticket de suporte.</p>
                <?= Html::a('<i class="fas fa-plus mr-2"></i>Criar Primeiro Ticket', ['ticket'], [
                    'class' => 'btn btn-primary btn-lg'
                ]) ?>
            </div>
        </div>
        <?php else: ?>

        <!-- Estatísticas -->
        <div class="row">
            <?php foreach ($stats as $status => $info): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="small-box bg-<?= $info['color'] ?>">
                    <div class="inner">
                        <h3><?= $counts[$status] ?? 0 ?></h3>
                        <p><?= $info['label'] ?></p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-<?= $info['icon'] ?>"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tickets List -->
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Lista de Tickets
                </h3>
                <div class="card-tools">
                    <span class="badge badge-primary"><?= count($tickets) ?> tickets</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px">#</th>
                                <th>Assunto</th>
                                <th style="width: 150px">Categoria</th>
                                <th style="width: 120px" class="text-center">Prioridade</th>
                                <th style="width: 140px" class="text-center">Estado</th>
                                <th style="width: 120px">Data</th>
                                <th style="width: 100px" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">#<?= $ticket->id ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= Html::encode($ticket->subject) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <?= Html::encode(mb_substr(strip_tags($ticket->body), 0, 60)) ?>...
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-tag"></i>
                                        <?= Html::encode($ticket->getCategoryLabel()) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $priorityColors[$ticket->priority] ?? 'secondary' ?>">
                                        <i class="fas fa-<?= $priorityIcons[$ticket->priority] ?? 'circle' ?>"></i>
                                        <?= Html::encode($ticket->getPriorityLabel()) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-<?= $statusColors[$ticket->status] ?? 'secondary' ?>">
                                        <i class="fas fa-<?= $statusIcons[$ticket->status] ?? 'circle' ?>"></i>
                                        <?= Html::encode($ticket->getStatusLabel()) ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <i class="far fa-calendar-alt"></i>
                                        <?= Yii::$app->formatter->asDate($ticket->created_at, 'php:d/m/Y') ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i>
                                        <?= Yii::$app->formatter->asTime($ticket->created_at, 'php:H:i') ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?= Html::a('<i class="fas fa-eye"></i>', ['ticket-view', 'id' => $ticket->id], [
                                        'class' => 'btn btn-sm btn-info',
                                        'title' => 'Ver Detalhes',
                                        'data-toggle' => 'tooltip'
                                    ]) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <div class="float-left">
                    <small class="text-muted">
                        Total: <strong><?= count($tickets) ?></strong> tickets
                    </small>
                </div>
                <div class="float-right">
                    <?= Html::a('<i class="fas fa-plus"></i> Novo Ticket', ['ticket'], [
                        'class' => 'btn btn-sm btn-primary'
                    ]) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php
$this->registerJs(<<<JS
    $('[data-toggle="tooltip"]').tooltip();
JS
);
?>
