<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $logs */
/** @var string $logFile */

$this->title = 'Logs do Sistema';
?>

<style>
    .log-container {
        background: #1e1e1e;
        color: #d4d4d4;
        border-radius: 8px;
        padding: 20px;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 12px;
        max-height: 600px;
        overflow-y: auto;
    }
    .log-line {
        padding: 2px 0;
        border-bottom: 1px solid #333;
    }
    .log-error {
        color: #f44336;
    }
    .log-warning {
        color: #ff9800;
    }
    .log-info {
        color: #2196f3;
    }
    .log-date {
        color: #888;
    }
</style>

<div class="system-logs">
    
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-file-alt mr-2"></i><?= Html::encode($this->title) ?></h2>
            <p class="text-muted">Ficheiro: <code><?= Html::encode($logFile) ?></code></p>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Voltar', ['system/settings'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::beginForm(['system/clear-logs'], 'post', ['style' => 'display:inline']) ?>
                <?= Html::submitButton('<i class="fas fa-eraser mr-1"></i> Limpar Logs', [
                    'class' => 'btn btn-danger',
                    'data-confirm' => 'Tem certeza que deseja limpar os logs?',
                ]) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endforeach; ?>

    <div class="card">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-terminal mr-2"></i>
            Últimas <?= count($logs) ?> entradas
            <span class="float-right text-muted">Atualizado: <?= date('Y-m-d H:i:s') ?></span>
        </div>
        <div class="card-body p-0">
            <div class="log-container">
                <?php if (empty($logs) || (count($logs) === 1 && empty($logs[0]))): ?>
                    <p class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                        Nenhum log registado.
                    </p>
                <?php else: ?>
                    <?php foreach ($logs as $line): ?>
                        <?php if (trim($line)): ?>
                            <div class="log-line <?php
                                if (stripos($line, 'error') !== false) echo 'log-error';
                                elseif (stripos($line, 'warning') !== false) echo 'log-warning';
                                elseif (stripos($line, 'info') !== false) echo 'log-info';
                            ?>">
                                <?= Html::encode($line) ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
