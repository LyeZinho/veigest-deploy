<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $critical */
/** @var array $warning */
/** @var array $counts */

$this->title = 'Alertas';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-sm-12">
            <h1 class="m-0">Alertas</h1>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (empty($critical) && empty($warning)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Nenhum alerta no momento.
                </div>
            <?php else: ?>
                <?php if (!empty($critical)): ?>
                    <div class="mb-4">
                        <h5 class="text-danger"><i class="fas fa-exclamation-circle"></i> Crítico (<?= count($critical) ?>)</h5>
                        <div class="list-group">
                            <?php foreach ($critical as $alert): ?>
                                <div class="list-group-item border-left border-danger">
                                    <h6 class="mb-1"><?= Html::encode($alert['title']) ?></h6>
                                    <p class="mb-2 small"><?= Html::encode($alert['message']) ?></p>
                                    <?php if (!empty($alert['action'])): ?>
                                        <?= Html::a('Resolver', $alert['action'], ['class' => 'btn btn-sm btn-outline-danger']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($warning)): ?>
                    <div class="mb-4">
                        <h5 class="text-warning"><i class="fas fa-exclamation-triangle"></i> Atenção (<?= count($warning) ?>)</h5>
                        <div class="list-group">
                            <?php foreach ($warning as $alert): ?>
                                <div class="list-group-item border-left border-warning">
                                    <h6 class="mb-1"><?= Html::encode($alert['title']) ?></h6>
                                    <p class="mb-2 small"><?= Html::encode($alert['message']) ?></p>
                                    <?php if (!empty($alert['action'])): ?>
                                        <?= Html::a('Resolver', $alert['action'], ['class' => 'btn btn-sm btn-outline-warning']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
