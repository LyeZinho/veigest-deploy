<?php

use yii\bootstrap5\Html;
?>
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <!-- LOGO -->
            <div class="flex items-center space-x-2">
                <img src="<?= Yii::getAlias('@web/images/veigest-logo.png') ?>" alt="VeiGest Logo" class="h-10 w-10">
                <span class="text-xl font-bold text-primary">VeiGest</span>
            </div>

            <!-- MENU -->
            <div class="hidden md:flex space-x-8">
                <?= Html::a('Início', ['/site/index'], [
                    'class' => 'text-gray-700 hover:text-primary transition',
                ]) ?>
                <?= Html::a('Sobre', ['/site/about'], [
                    'class' => 'text-gray-700 hover:text-primary transition',
                ]) ?>
                <?= Html::a('Serviços', ['/site/services'], [
                    'class' => 'text-gray-700 hover:text-primary transition',
                ]) ?>
                <?= Html::a('Benefícios', ['/site/benefits'], [
                    'class' => 'text-gray-700 hover:text-primary transition',
                ]) ?>
                <?= Html::a('Preços', ['/site/pricing'], [
                    'class' => 'text-gray-700 hover:text-primary transition',
                ]) ?>
                <?= Html::a('Contactos', ['/site/contact'], [
                    'class' => 'text-gray-700 hover:text-primary transition',
                ]) ?>


                <?php if (!Yii::$app->user->isGuest): ?>
                    <?= Html::a('Suporte', ['/site/ticket'], [
                        'class' => 'text-gray-700 hover:text-primary transition',
                    ]) ?>
                <?php endif; ?>

            </div>

            <!-- AUTENTICAÇÃO -->
            <div class="flex items-center space-x-4">

                <?php if (Yii::$app->user->isGuest): ?>

                    <?= Html::a('Entrar', ['/site/login'], [
                        'class' => 'text-gray-700 hover:text-primary font-medium px-4 py-2 rounded transition',
                    ]) ?>

                    <?= Html::a('Registar', ['/site/signup'], [
                        'class' => 'btn-primary px-6 py-2 rounded-lg font-medium hover:opacity-90',
                    ]) ?>

                <?php else: ?>

                    <div style="display: flex; gap: 12px; align-items: center;">

                        <!-- DASHBOARD / BACKEND BUTTON -->
                        <?php if (!Yii::$app->user->isGuest): ?>

                            <?php $role = Yii::$app->user->identity->role; ?>

                            <?php if ($role === 'admin'): ?>
                                <!-- Admin: Only Backend access -->
                                <a href="<?= Yii::getAlias('@backendUrl') ?>"
                                    class="btn btn-dark px-4 py-2">
                                    <i class="fas fa-cogs me-2"></i> Backoffice
                                </a>

                            <?php elseif ($role === 'manager' || $role === 'driver'): ?>
                                <!-- Manager/Driver: Frontend Dashboard access -->
                                <a href="<?= \yii\helpers\Url::to(['/dashboard/index']) ?>"
                                    class="btn btn-success px-4 py-2">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>

                            <?php endif; ?>

                        <?php endif; ?>


                        <!-- LOGOUT -->
                        <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-inline-block', 'id' => 'logout-form'])
                        . Html::submitButton(
                            'Sair (' . Html::encode(Yii::$app->user->identity->username) . ')',
                            ['class' => 'btn btn-danger px-4 py-2 rounded font-medium']
                        )
                        . Html::endForm();
                    ?>

                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</nav>