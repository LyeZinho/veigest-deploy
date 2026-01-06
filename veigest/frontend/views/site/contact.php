<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\ContactForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Contacto';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="bg-gray-50">

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-black">Contacte-nos</h1>
            <p class="text-lg text-opacity-90 text-black">Entre em contacto connosco. Estamos prontos para ajudar a sua empresa</p>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 mb-16">
                <!-- Contact Details -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">Informações de Contacto</h2>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                                    <i class="fas fa-map-marker-alt text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Morada</h3>
                                <p class="text-gray-600">Rua da Inovação, 123<br>Lisboa, Portugal<br>1000-001</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                                    <i class="fas fa-phone text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Telefone</h3>
                                <p class="text-gray-600">+351 21 0000 000<br>+351 91 000 0000 (WhatsApp)</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                                    <i class="fas fa-envelope text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Email</h3>
                                <p class="text-gray-600">info@veigest.com<br>support@veigest.com</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                                    <i class="fas fa-clock text-xl"></i>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Horário de Funcionamento</h3>
                                <p class="text-gray-600">Segunda - Sexta: 9:00 - 18:00<br>Sábado: 9:00 - 13:00<br>Domingo: Encerrado</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">Localização</h2>
                    <div class="bg-gray-200 rounded-lg h-80 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt text-4xl text-primary mb-4"></i>
                            <p class="text-gray-600">Mapa interativo</p>
                            <p class="text-sm text-gray-500">Lisboa, Portugal</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Options -->
            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-handshake text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Comercial</h3>
                    <p class="text-gray-600 mb-4">Informações sobre produtos e preços</p>
                    <a href="mailto:comercial@veigest.com" class="text-primary hover:underline font-medium">comercial@veigest.com</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-tools text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Suporte Técnico</h3>
                    <p class="text-gray-600 mb-4">Ajuda com produtos e funcionalidades</p>
                    <a href="mailto:support@veigest.com" class="text-primary hover:underline font-medium">support@veigest.com</a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-building text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Parcerias</h3>
                    <p class="text-gray-600 mb-4">Oportunidades de parceria e integração</p>
                    <a href="mailto:parcerias@veigest.com" class="text-primary hover:underline font-medium">parcerias@veigest.com</a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Envie-nos uma Mensagem</h2>
                <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>
                <div class="space-y-4">
                    <?= $form->field($model, 'name')->textInput([
                        'autofocus' => true,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'O seu nome'
                    ])->label('Nome', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'email')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'seu.email@empresa.com'
                    ])->label('Email', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'subject')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Qual é o tema?'
                    ])->label('Assunto', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'body')->textarea([
                        'rows' => 5,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Descreva o seu problema'
                    ])->label('Mensagem', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'template' => '<div class="mb-4"><div class="flex items-center space-x-4">{image}{input}</div></div>',
                        'options' => ['class' => 'px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary']
                    ])->label('Código de Verificação', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Enviar Mensagem', [
                            'class' => 'w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-opacity-90 transition',
                            'name' => 'contact-button'
                        ]) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

            <!-- Social Media & Additional Info -->
            <div class="mt-16 pt-16 border-t border-gray-200">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-8">Siga-nos nas Redes Sociais</h2>
                    <div class="flex justify-center space-x-6 mb-8">
                        <a href="#" class="bg-primary text-white p-3 rounded-full hover:bg-opacity-90 transition">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                        <a href="#" class="bg-primary text-white p-3 rounded-full hover:bg-opacity-90 transition">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="bg-primary text-white p-3 rounded-full hover:bg-opacity-90 transition">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="bg-primary text-white p-3 rounded-full hover:bg-opacity-90 transition">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="bg-primary text-white p-3 rounded-full hover:bg-opacity-90 transition">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-8 max-w-2xl mx-auto">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Newsletter</h3>
                        <p class="text-gray-600 mb-6">Mantenha-se atualizado com as últimas novidades da VeiGest</p>
                        <div class="flex gap-4">
                            <input type="email" placeholder="O seu email" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary">
                            <button class="bg-primary text-white px-6 py-3 rounded-lg font-bold hover:bg-opacity-90 transition">
                                Subscrever
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


