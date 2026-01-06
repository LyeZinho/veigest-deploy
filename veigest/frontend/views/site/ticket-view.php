<?php

use yii\helpers\Html;
use common\models\SupportTicket;

/** @var yii\web\View $this */
/** @var common\models\SupportTicket $ticket */

$this->title = 'Ticket #' . $ticket->id;
$this->params['breadcrumbs'][] = ['label' => 'Meus Tickets', 'url' => ['my-tickets']];
$this->params['breadcrumbs'][] = '#' . $ticket->id;

$priorityColors = [
    'low' => 'bg-gray-100 text-gray-800',
    'medium' => 'bg-blue-100 text-blue-800',
    'high' => 'bg-orange-100 text-orange-800',
    'urgent' => 'bg-red-100 text-red-800',
];

$statusColors = [
    'open' => 'bg-green-100 text-green-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
    'waiting_response' => 'bg-yellow-100 text-yellow-800',
    'resolved' => 'bg-purple-100 text-purple-800',
    'closed' => 'bg-gray-100 text-gray-800',
];
?>

<div class="container-fluid px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <i class="fas fa-ticket-alt mr-2 text-primary"></i>Ticket #<?= $ticket->id ?>
        </h1>
        <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Voltar', ['my-tickets'], [
            'class' => 'bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition'
        ]) ?>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="md:col-span-2">
            <!-- Ticket Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold text-gray-900"><?= Html::encode($ticket->subject) ?></h2>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' ?>">
                            <?= $ticket->getPriorityLabel() ?>
                        </span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' ?>">
                            <?= $ticket->getStatusLabel() ?>
                        </span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Descrição do Problema</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 whitespace-pre-wrap"><?= Html::encode($ticket->body) ?></p>
                    </div>
                </div>
                
                <div class="text-sm text-gray-500">
                    <i class="fas fa-calendar mr-1"></i>
                    Criado em <?= Yii::$app->formatter->asDatetime($ticket->created_at, 'php:d/m/Y H:i') ?>
                </div>
            </div>

            <!-- Response Section -->
            <?php if ($ticket->admin_response): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-reply text-primary mr-2"></i>Resposta da Equipa
                </h3>
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-4">
                    <p class="text-gray-700 whitespace-pre-wrap"><?= Html::encode($ticket->admin_response) ?></p>
                </div>
                <?php if ($ticket->responded_at): ?>
                <div class="mt-3 text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i>
                    Respondido em <?= Yii::$app->formatter->asDatetime($ticket->responded_at, 'php:d/m/Y H:i') ?>
                    <?php if ($ticket->responder): ?>
                    por <?= Html::encode($ticket->responder->name ?? $ticket->responder->username) ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-clock text-yellow-400 text-xl mr-3"></i>
                    <div>
                        <h4 class="font-medium text-yellow-800">Aguardando Resposta</h4>
                        <p class="text-sm text-yellow-700">A nossa equipa ainda não respondeu a este ticket. Entraremos em contacto brevemente.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-info-circle text-primary mr-2"></i>Detalhes
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">ID do Ticket</label>
                        <p class="text-gray-900 font-semibold">#<?= $ticket->id ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Estado</label>
                        <p>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-800' ?>">
                                <?= $ticket->getStatusLabel() ?>
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Prioridade</label>
                        <p>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-800' ?>">
                                <?= $ticket->getPriorityLabel() ?>
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Categoria</label>
                        <p class="text-gray-900"><?= $ticket->getCategoryLabel() ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Data de Criação</label>
                        <p class="text-gray-900"><?= Yii::$app->formatter->asDatetime($ticket->created_at, 'php:d/m/Y H:i') ?></p>
                    </div>
                    
                    <?php if ($ticket->responded_at): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Data da Resposta</label>
                        <p class="text-gray-900"><?= Yii::$app->formatter->asDatetime($ticket->responded_at, 'php:d/m/Y H:i') ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <hr class="my-4">
                
                <div class="space-y-2">
                    <?= Html::a('<i class="fas fa-plus mr-2"></i>Novo Ticket', ['ticket'], [
                        'class' => 'block w-full text-center bg-primary text-white px-4 py-2 rounded-lg hover:bg-opacity-90 transition'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-list mr-2"></i>Todos os Tickets', ['my-tickets'], [
                        'class' => 'block w-full text-center bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-purple-100 { background-color: #f3e8ff; }
.text-purple-800 { color: #5b21b6; }
</style>
