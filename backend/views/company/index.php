<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use yii\grid\GridView;

$this->title = 'Empresas';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="company-index">
    <div class="row mb-3">
        <div class="col-md-6">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('Criar Empresa', ['create'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'code',
            'name',
            'tax_id',
            'email:email',
            'phone',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    return $model->status === 'active' ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>';
                },
                'format' => 'html',
            ],
            'created_at',
            [
                'class' => 'yii\grid\ActionColumn',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-info',
                            'title' => 'Ver',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-warning',
                            'title' => 'Editar',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-danger',
                            'title' => 'Eliminar',
                            'data' => [
                                'confirm' => 'Tem a certeza que deseja eliminar?',
                                'method' => 'post',
                            ],
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
