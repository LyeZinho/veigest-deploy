<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\ProfileForm $model */

$this->title = 'Editar Perfil';
$this->params['breadcrumbs'][] = ['label' => 'Dashboard', 'url' => ['dashboard/index']];
$this->params['breadcrumbs'][] = ['label' => 'Meu Perfil', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$user = $model->getUser();
$photoUrl = $user->photo 
    ? $user->photo 
    : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=150&background=09BC8A&color=fff';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">
                    <i class="fas fa-user-edit mr-2"></i><?= Html::encode($this->title) ?>
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
                            <i class="fas fa-edit mr-2"></i>Editar Informações Pessoais
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php $form = ActiveForm::begin([
                            'options' => ['enctype' => 'multipart/form-data'],
                            'fieldConfig' => [
                                'template' => "{label}\n{input}\n{hint}\n{error}",
                                'labelOptions' => ['class' => 'form-label font-weight-bold'],
                                'inputOptions' => ['class' => 'form-control'],
                                'errorOptions' => ['class' => 'invalid-feedback d-block'],
                            ],
                        ]); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'name')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Nome completo'
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'email')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'email@exemplo.com'
                                ]) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'phone')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => '+351 912 345 678'
                                ])->hint('Formato: +351 912 345 678') ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-weight-bold">Nome de Utilizador</label>
                                <input type="text" class="form-control" value="<?= Html::encode($user->username) ?>" disabled>
                                <small class="text-muted">O nome de utilizador não pode ser alterado.</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="fas fa-id-card-alt mr-2"></i>Dados de Condutor</h5>
                        <p class="text-muted small mb-3">Preencha apenas se for condutor.</p>

                        <div class="row">
                            <div class="col-md-6">
                                <?= $form->field($model, 'license_number')->textInput([
                                    'maxlength' => true,
                                    'placeholder' => 'Ex: PT-123456789'
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $form->field($model, 'license_expiry')->textInput([
                                    'type' => 'date'
                                ])->hint('Data de validade da carta de condução') ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="fas fa-camera mr-2"></i>Foto de Perfil</h5>

                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <img src="<?= Html::encode($photoUrl) ?>" 
                                     alt="Foto atual" 
                                     class="img-fluid rounded-circle mb-2"
                                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #09BC8A;"
                                     id="preview-photo">
                                <p class="small text-muted">Foto atual</p>
                            </div>
                            <div class="col-md-8">
                                <?= $form->field($model, 'photoFile')->fileInput([
                                    'class' => 'form-control',
                                    'accept' => 'image/png, image/jpeg, image/gif',
                                    'id' => 'photo-input'
                                ])->hint('Formatos aceites: PNG, JPG, GIF. Tamanho máximo: 2MB.') ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="form-group text-right">
                            <?= Html::a('Cancelar', ['index'], ['class' => 'btn btn-secondary mr-2']) ?>
                            <?= Html::submitButton('<i class="fas fa-save mr-2"></i>Guardar Alterações', [
                                'class' => 'btn btn-primary'
                            ]) ?>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Card de Dicas -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-lightbulb mr-2"></i>Dicas
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li class="mb-2">Mantenha seu email atualizado para receber notificações importantes.</li>
                            <li class="mb-2">A foto de perfil ajuda outros utilizadores a identificá-lo.</li>
                            <li class="mb-2">Se for condutor, mantenha os dados da carta de condução atualizados.</li>
                            <li>Todas as alterações são registadas no histórico.</li>
                        </ul>
                    </div>
                </div>

                <!-- Card de Alteração de Senha -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-key mr-2"></i>Palavra-passe
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">
                            Para alterar a sua palavra-passe, utilize a página dedicada.
                        </p>
                        <?= Html::a('<i class="fas fa-key mr-2"></i>Alterar Palavra-passe', ['change-password'], [
                            'class' => 'btn btn-warning btn-block'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$js = <<<JS
// Preview da foto antes do upload
document.getElementById('photo-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-photo').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
JS;
$this->registerJs($js);
?>

<style>
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}
.form-label {
    color: #495057;
}
</style>
