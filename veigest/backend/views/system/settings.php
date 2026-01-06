<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var array $systemInfo */
/** @var array $dbStats */

$this->title = 'Configurações do Sistema';
?>

<style>
    .settings-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .settings-card .card-header {
        background: linear-gradient(135deg, #09BC8A 0%, #07a078 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }
    .stat-card {
        border-left: 4px solid #09BC8A;
        background: #f8f9fa;
    }
    .test-btn {
        margin: 5px;
    }
    .info-table td {
        padding: 8px 12px;
    }
    .info-table td:first-child {
        font-weight: 600;
        width: 40%;
    }
    .status-ok { color: #28a745; }
    .status-warning { color: #ffc107; }
    .status-error { color: #dc3545; }
</style>

<div class="system-settings">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-cogs mr-2"></i><?= Html::encode($this->title) ?></h2>
            <p class="text-muted">Gerir configurações do sistema, executar testes e monitorizar a saúde da aplicação.</p>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
            <?= $message ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endforeach; ?>

    <div class="row">
        
        <!-- System Stats -->
        <div class="col-lg-8">
            
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0"><?= $dbStats['users_count'] ?? 0 ?></h3>
                            <small class="text-muted">Utilizadores</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0"><?= $dbStats['vehicles_count'] ?? 0 ?></h3>
                            <small class="text-muted">Veículos</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0"><?= $dbStats['companies_count'] ?? 0 ?></h3>
                            <small class="text-muted">Empresas</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body text-center">
                            <h3 class="mb-0"><?= $dbStats['documents_count'] ?? 0 ?></h3>
                            <small class="text-muted">Documentos</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card settings-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-server mr-2"></i>Informações do Sistema</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped info-table mb-0">
                        <tr>
                            <td>Versão PHP</td>
                            <td><span class="badge badge-info"><?= $systemInfo['php_version'] ?></span></td>
                        </tr>
                        <tr>
                            <td>Versão Yii2</td>
                            <td><span class="badge badge-primary"><?= $systemInfo['yii_version'] ?></span></td>
                        </tr>
                        <tr>
                            <td>Servidor Web</td>
                            <td><?= Html::encode($systemInfo['server_software']) ?></td>
                        </tr>
                        <tr>
                            <td>Base de Dados</td>
                            <td>
                                <span class="badge badge-secondary"><?= $dbStats['db_driver'] ?? 'N/A' ?></span>
                                <?= $dbStats['db_name'] ?? '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Limite de Memória</td>
                            <td><?= $systemInfo['memory_limit'] ?></td>
                        </tr>
                        <tr>
                            <td>Tamanho Máx. Upload</td>
                            <td><?= $systemInfo['upload_max_filesize'] ?></td>
                        </tr>
                        <tr>
                            <td>Fuso Horário</td>
                            <td><?= $systemInfo['timezone'] ?></td>
                        </tr>
                        <tr>
                            <td>Espaço em Disco</td>
                            <td>
                                <span class="text-success"><?= $systemInfo['disk_free_space'] ?></span> livre 
                                / <?= $systemInfo['disk_total_space'] ?> total
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Test & Maintenance Tools -->
            <div class="card settings-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tools mr-2"></i>Ferramentas de Teste & Manutenção</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-database mr-1"></i> Base de Dados</h6>
                            <?= Html::beginForm(['system/test-database'], 'post') ?>
                                <?= Html::submitButton('<i class="fas fa-plug mr-1"></i> Testar Conexão', [
                                    'class' => 'btn btn-outline-primary test-btn',
                                ]) ?>
                            <?= Html::endForm() ?>
                            <small class="text-muted d-block mt-2">Verifica se a ligação à base de dados está funcional.</small>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-envelope mr-1"></i> Email</h6>
                            <?= Html::beginForm(['system/test-email'], 'post') ?>
                                <?= Html::submitButton('<i class="fas fa-paper-plane mr-1"></i> Enviar Email Teste', [
                                    'class' => 'btn btn-outline-info test-btn',
                                ]) ?>
                            <?= Html::endForm() ?>
                            <small class="text-muted d-block mt-2">Envia um email de teste para verificar configurações SMTP.</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-broom mr-1"></i> Cache</h6>
                            <?= Html::beginForm(['system/clear-cache'], 'post') ?>
                                <?= Html::submitButton('<i class="fas fa-trash mr-1"></i> Limpar Cache', [
                                    'class' => 'btn btn-outline-warning test-btn',
                                    'data-confirm' => 'Tem certeza que deseja limpar o cache?',
                                ]) ?>
                            <?= Html::endForm() ?>
                            <small class="text-muted d-block mt-2">Remove ficheiros temporários do sistema.</small>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-file-alt mr-1"></i> Logs</h6>
                            <?= Html::a('<i class="fas fa-eye mr-1"></i> Ver Logs', ['system/logs'], [
                                'class' => 'btn btn-outline-secondary test-btn',
                            ]) ?>
                            <?= Html::beginForm(['system/clear-logs'], 'post', ['style' => 'display:inline']) ?>
                                <?= Html::submitButton('<i class="fas fa-eraser mr-1"></i> Limpar Logs', [
                                    'class' => 'btn btn-outline-danger test-btn',
                                    'data-confirm' => 'Tem certeza que deseja limpar os logs?',
                                ]) ?>
                            <?= Html::endForm() ?>
                            <small class="text-muted d-block mt-2">Visualizar ou limpar registos de erros.</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            
            <!-- Quick Actions -->
            <div class="card settings-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt mr-2"></i>Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?= Html::a('<i class="fas fa-users mr-2"></i> Gerir Utilizadores', ['/user/index'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-user-plus mr-2"></i> Criar Utilizador', ['/user/create'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <?= Html::a('<i class="fas fa-info-circle mr-2"></i> Info do Sistema', ['system/info'], ['class' => 'list-group-item list-group-item-action']) ?>
                        <a href="<?= Yii::getAlias('@frontendUrl') ?>" target="_blank" class="list-group-item list-group-item-action">
                            <i class="fas fa-external-link-alt mr-2"></i> Abrir Frontend
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="card settings-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-heartbeat mr-2"></i>Estado do Sistema</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle status-ok mr-2"></i>
                            Aplicação Online
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle status-ok mr-2"></i>
                            Base de Dados Conectada
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle status-ok mr-2"></i>
                            <?= $dbStats['users_active'] ?? 0 ?> utilizadores ativos
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info mr-2"></i>
                            Versão VeiGest 1.0.0
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Environment Info -->
            <div class="card settings-card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-code mr-2"></i>Ambiente</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Modo</td>
                            <td>
                                <?php if (YII_ENV_DEV): ?>
                                    <span class="badge badge-warning">Development</span>
                                <?php elseif (YII_ENV_PROD): ?>
                                    <span class="badge badge-success">Production</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Test</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Debug</td>
                            <td>
                                <?php if (YII_DEBUG): ?>
                                    <span class="badge badge-warning">Ativo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Desativo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Servidor</td>
                            <td><small><?= $_SERVER['SERVER_NAME'] ?? 'localhost' ?></small></td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
