<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\models\Driver;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Condutores';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = $this->title;

// Mantém links clicáveis sem cor azul e mostra setas de ordenação
$this->registerCss('
.grid-view a { color: inherit; text-decoration: none; }
.grid-view a:hover { color: inherit; text-decoration: underline; }
.grid-view th a.asc:after { content: " \25B2"; font-size: 11px; }
.grid-view th a.desc:after { content: " \25BC"; font-size: 11px; }
');

?>

<div class="content-header">
    <div class="row justify-content-center mb-2">
        <div class="col-12 col-xl-11 col-xxl-10 d-flex align-items-center justify-content-between flex-wrap">
            <h1 class="m-0"><?= Html::encode($this->title) ?></h1>
            <?php if (Yii::$app->user->can('drivers.create')): ?>
                <?= Html::a('Novo Condutor', ['driver/create'], ['class' => 'btn btn-primary btn-sm']) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11 col-xxl-10">
            <div class="card w-100" style="max-width: 1200px; margin: 0 auto;">
                <div class="card-header">
                    <h3 class="card-title">Lista de Condutores</h3>
                    <!-- Botão duplicado removido; já existe no header -->
                </div>
                <div class="card-body p-0">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'summary' => '<div class="pl-3 pt-2">Mostrando <strong>{begin}-{end}</strong> de <strong>{totalCount}</strong> itens.</div>',
                        'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                        'headerRowOptions' => ['style' => 'background:#f7f9fb;'],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'label' => 'Nome',
                                'format' => 'text',
                                'contentOptions' => ['style' => 'min-width:160px;']
                            ],
                            [
                                'attribute' => 'email',
                                'format' => 'text',
                                'contentOptions' => ['style' => 'min-width:200px;']
                            ],
                            [
                                'attribute' => 'phone',
                                'label' => 'Telefone',
                                'contentOptions' => ['style' => 'min-width:140px;']
                            ],
                            [
                                'attribute' => 'license_number',
                                'label' => 'Número da Carta',
                                'contentOptions' => ['style' => 'min-width:150px;']
                            ],
                            [
                                'attribute' => 'status',
                                'label' => 'Estado',
                                'value' => function($model) {
                                    $isActive = $model->status === 10;
                                    $status = $isActive ? 'Ativo' : 'Inativo';
                                    $badgeClass = $isActive ? 'badge-success' : 'badge-secondary';
                                    return '<span class="badge ' . $badgeClass . '">' . $status . '</span>';
                                },
                                'format' => 'html',
                                'contentOptions' => ['style' => 'width:110px;']
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'controller' => 'driver',
                                'header' => 'Ações',
                                'headerOptions' => ['style' => 'width:200px; text-align:center;'],
                                'contentOptions' => ['style' => 'text-align:center; white-space:nowrap;'],
                                'template' => '{view} {update} {delete}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Yii::$app->user->can('drivers.view')
                                            ? Html::a('Ver', $url, ['class' => 'btn btn-outline-info btn-sm me-1'])
                                            : '';
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Yii::$app->user->can('drivers.update')
                                            ? Html::a('Editar', $url, ['class' => 'btn btn-outline-warning btn-sm me-1'])
                                            : '';
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        return Yii::$app->user->can('drivers.delete')
                                            ? Html::a('Apagar', $url, [
                                                'class' => 'btn btn-outline-danger btn-sm',
                                                'data' => [
                                                    'confirm' => 'Tem a certeza que deseja apagar este condutor?',
                                                    'method' => 'post',
                                                ],
                                            ])
                                            : '';
                                    },
                                ],
                            ],
                        ],
                    ]) ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
