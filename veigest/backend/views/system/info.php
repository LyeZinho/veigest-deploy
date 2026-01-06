<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $systemInfo */
/** @var array $dbStats */
/** @var array $phpInfo */

$this->title = 'Informações do Sistema';
?>

<div class="system-info">
    
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-info-circle mr-2"></i><?= Html::encode($this->title) ?></h2>
            <p class="text-muted">Informações detalhadas sobre o ambiente do servidor.</p>
        </div>
        <div class="col-md-4 text-right">
            <?= Html::a('<i class="fas fa-arrow-left mr-1"></i> Voltar', ['system/settings'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        
        <!-- Server Info -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-server mr-2"></i>Servidor</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped mb-0">
                        <?php foreach ($systemInfo as $key => $value): ?>
                        <tr>
                            <td class="font-weight-bold" style="width:50%"><?= ucwords(str_replace('_', ' ', $key)) ?></td>
                            <td><?= Html::encode($value) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Database Info -->
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-database mr-2"></i>Base de Dados</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($dbStats['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= Html::encode($dbStats['error']) ?>
                        </div>
                    <?php else: ?>
                        <table class="table table-striped mb-0">
                            <tr>
                                <td class="font-weight-bold">Driver</td>
                                <td><span class="badge badge-info"><?= $dbStats['db_driver'] ?? 'N/A' ?></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Base de Dados</td>
                                <td><?= $dbStats['db_name'] ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Total Utilizadores</td>
                                <td><?= $dbStats['users_count'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Utilizadores Ativos</td>
                                <td><?= $dbStats['users_active'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Total Veículos</td>
                                <td><?= $dbStats['vehicles_count'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Total Empresas</td>
                                <td><?= $dbStats['companies_count'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Total Manutenções</td>
                                <td><?= $dbStats['maintenances_count'] ?? 0 ?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Total Documentos</td>
                                <td><?= $dbStats['documents_count'] ?? 0 ?></td>
                            </tr>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- PHP Extensions -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-puzzle-piece mr-2"></i>Extensões PHP Carregadas</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php 
                $extensions = $phpInfo['extensions'] ?? [];
                sort($extensions);
                foreach ($extensions as $ext): 
                ?>
                <div class="col-md-2 col-sm-3 col-4 mb-2">
                    <span class="badge badge-light"><?= Html::encode($ext) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- PDO Drivers -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-plug mr-2"></i>Drivers PDO Disponíveis</h5>
        </div>
        <div class="card-body">
            <?php foreach ($phpInfo['pdo_drivers'] ?? [] as $driver): ?>
                <span class="badge badge-primary mr-2 mb-2" style="font-size:14px">
                    <?= Html::encode($driver) ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>

</div>
