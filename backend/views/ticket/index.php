<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\SupportTicket;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $stats */
/** @var string|null $currentStatus */
/** @var string|null $currentPriority */
/** @var string|null $currentCategory */

$this->title = 'Tickets de Suporte';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-ticket-alt mr-2"></i>Tickets de Suporte
                </h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <a href="<?= Url::to(['index', 'status' => 'open']) ?>" class="text-decoration-none">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $stats['open'] ?></h3>
                            <p>Abertos</p>
                        </div>
                        <div class="icon"><i class="fas fa-envelope-open"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= Url::to(['index', 'status' => 'in_progress']) ?>" class="text-decoration-none">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?= $stats['in_progress'] ?></h3>
                            <p>Em Progresso</p>
                        </div>
                        <div class="icon"><i class="fas fa-spinner"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= Url::to(['index', 'status' => 'waiting_response']) ?>" class="text-decoration-none">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 style="color:white;"><?= $stats['waiting_response'] ?></h3>
                            <p style="color:white;">Aguardando</p>
                        </div>
                        <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= Url::to(['index', 'status' => 'resolved']) ?>" class="text-decoration-none">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3><?= $stats['resolved'] ?></h3>
                            <p>Resolvidos</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= Url::to(['index', 'status' => 'closed']) ?>" class="text-decoration-none">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3><?= $stats['closed'] ?></h3>
                            <p>Fechados</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </a>
            </div>
            <div class="col-md-2">
                <a href="<?= Url::to(['index']) ?>" class="text-decoration-none">
                    <div class="small-box bg-dark">
                        <div class="inner">
                            <h3><?= $stats['total'] ?></h3>
                            <p>Total</p>
                        </div>
                        <div class="icon"><i class="fas fa-ticket-alt"></i></div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filtros</h3>
            </div>
            <div class="card-body">
                <form method="get" class="row">
                    <div class="col-md-3">
                        <label>Estado</label>
                        <select name="status" class="form-control">
                            <option value="all" <?= $currentStatus === 'all' || !$currentStatus ? 'selected' : '' ?>>Todos</option>
                            <?php foreach (SupportTicket::getStatusOptions() as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $currentStatus === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Prioridade</label>
                        <select name="priority" class="form-control">
                            <option value="all" <?= $currentPriority === 'all' || !$currentPriority ? 'selected' : '' ?>>Todas</option>
                            <?php foreach (SupportTicket::getPriorityOptions() as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $currentPriority === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Categoria</label>
                        <select name="category" class="form-control">
                            <option value="all" <?= $currentCategory === 'all' || !$currentCategory ? 'selected' : '' ?>>Todas</option>
                            <?php foreach (SupportTicket::getCategoryOptions() as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $currentCategory === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i>Filtrar
                            </button>
                            <a href="<?= Url::to(['index']) ?>" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Lista de Tickets</h3>
            </div>
            <div class="card-body p-0">
                <?php Pjax::begin(['id' => 'tickets-grid']); ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-striped table-hover mb-0'],
                    'layout' => "{items}\n{pager}",
                    'columns' => [
                        [
                            'attribute' => 'id',
                            'label' => '#',
                            'headerOptions' => ['style' => 'width: 60px;'],
                            'format' => 'raw',
                            'value' => function($model) {
                                return '<strong>#' . $model->id . '</strong>';
                            },
                        ],
                        [
                            'attribute' => 'subject',
                            'label' => 'Assunto',
                            'format' => 'raw',
                            'value' => function($model) {
                                return Html::a(Html::encode($model->subject), ['view', 'id' => $model->id], ['class' => 'text-primary font-weight-bold']);
                            },
                        ],
                        [
                            'attribute' => 'name',
                            'label' => 'Utilizador',
                            'format' => 'raw',
                            'value' => function($model) {
                                $icon = $model->user_id ? '<i class="fas fa-user text-success mr-1" title="Utilizador registado"></i>' : '<i class="fas fa-user-secret text-muted mr-1" title="Visitante"></i>';
                                return $icon . Html::encode($model->name) . '<br><small class="text-muted">' . Html::encode($model->email) . '</small>';
                            },
                        ],
                        [
                            'attribute' => 'priority',
                            'label' => 'Prioridade',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 100px;'],
                            'value' => function($model) {
                                $colors = [
                                    'low' => 'secondary',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger',
                                ];
                                $color = $colors[$model->priority] ?? 'secondary';
                                return '<span class="badge badge-' . $color . '">' . $model->getPriorityLabel() . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'category',
                            'label' => 'Categoria',
                            'headerOptions' => ['style' => 'width: 150px;'],
                            'value' => function($model) {
                                return $model->getCategoryLabel();
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'label' => 'Estado',
                            'format' => 'raw',
                            'headerOptions' => ['style' => 'width: 130px;'],
                            'value' => function($model) {
                                $colors = [
                                    'open' => 'success',
                                    'in_progress' => 'info',
                                    'waiting_response' => 'warning',
                                    'resolved' => 'purple',
                                    'closed' => 'secondary',
                                ];
                                $color = $colors[$model->status] ?? 'secondary';
                                return '<span class="badge badge-' . $color . '">' . $model->getStatusLabel() . '</span>';
                            },
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => 'Criado Em',
                            'headerOptions' => ['style' => 'width: 120px;'],
                            'format' => ['date', 'php:d/m/Y H:i'],
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['style' => 'width: 80px;'],
                            'template' => '{view} {delete}',
                            'buttons' => [
                                'view' => function($url, $model) {
                                    return Html::a('<i class="fas fa-eye"></i>', $url, [
                                        'class' => 'btn btn-sm btn-primary',
                                        'title' => 'Ver Detalhes',
                                    ]);
                                },
                                'delete' => function($url, $model) {
                                    if (Yii::$app->user->can('admin')) {
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'class' => 'btn btn-sm btn-danger ml-1',
                                            'title' => 'Eliminar',
                                            'data' => [
                                                'confirm' => 'Tem certeza que deseja eliminar este ticket?',
                                                'method' => 'post',
                                            ],
                                        ]);
                                    }
                                    return '';
                                },
                            ],
                        ],
                    ],
                ]); ?>
                <?php Pjax::end(); ?>
            </div>
        </div>

    </div>
</section>

<style>
.badge-purple {
    background-color: #6f42c1;
    color: white;
}
.bg-purple {
    background-color: #6f42c1 !important;
    color: white;
}
.bg-purple .icon {
    color: rgba(255,255,255,0.2);
}
</style>
