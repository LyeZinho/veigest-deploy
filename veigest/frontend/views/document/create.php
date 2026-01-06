<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var frontend\models\DocumentUploadForm $model */
/** @var common\models\Vehicle[] $vehicles */
/** @var common\models\User[] $drivers */

$this->title = 'Upload de Documento';
$this->params['breadcrumbs'][] = ['label' => 'Gestão Documental', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-upload mr-2"></i><?= Html::encode($this->title) ?>
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <?= Html::a('<i class="fas fa-arrow-left mr-2"></i>Voltar', ['index'], [
                    'class' => 'btn btn-secondary'
                ]) ?>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-file-upload mr-2"></i>Dados do Documento
                        </h3>
                    </div>
                    <div class="card-body">
                        <?= $this->render('_form', [
                            'model' => $model,
                            'vehicles' => $vehicles,
                            'drivers' => $drivers,
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>Informações
                        </h3>
                    </div>
                    <div class="card-body">
                        <h6><i class="fas fa-file-alt mr-2 text-primary"></i>Formatos Aceites</h6>
                        <p class="text-muted small">PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF</p>
                        
                        <hr>
                        
                        <h6><i class="fas fa-weight mr-2 text-primary"></i>Tamanho Máximo</h6>
                        <p class="text-muted small">10 MB por ficheiro</p>
                        
                        <hr>
                        
                        <h6><i class="fas fa-lightbulb mr-2 text-warning"></i>Dicas</h6>
                        <ul class="text-muted small pl-3">
                            <li>Preencha a data de validade para receber alertas automáticos</li>
                            <li>Associe o documento a um veículo ou motorista para melhor organização</li>
                            <li>Use as observações para adicionar informações importantes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
