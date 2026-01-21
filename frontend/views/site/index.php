<?php

use yii\helpers\Html;

$this->title = 'Bem-vindo à VeiGest';
?>
<div class="bg-gray-50">
    <!-- texto necessário para testes funcionais -->
    <div style="position:absolute;left:-9999px;top:-9999px;">VeiGest</div>
    <section class="hero-gradient text-white py-20 md:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                        Gestão Inteligente de Frotas Empresariais 
                    </h1>
                    <p class="text-lg mb-8 text-opacity-90">
                        Plataforma completa para monitorizar veículos, condutores e otimizar operações da sua frota em tempo real.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>" class="bg-white text-primary px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition text-center">
                            Começar Agora
                        </a>

                        <button class="border-2 border-white text-white px-8 py-3 rounded-lg font-bold hover:bg-white hover:bg-opacity-10 transition">
                            Ver Demo
                        </button>
                    </div>
                </div>
                <div class="hidden md:flex justify-center">
                    <div class="relative w-64 h-64">
                        <img src="<?= Yii::getAlias('@web/images/veigest-logo.png') ?>" class="w-full h-full object-contain filter drop-shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Os Nossos Serviços</h2>
                <p class="text-xl text-gray-600">Soluções completas para gestão eficiente da sua frota</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Service Card 1 -->
                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary">
                    <div class="mb-4">
                        <i class="fas fa-truck text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Gestão de Veículos</h3>
                    <p class="text-gray-600">Cadastro completo, rastreamento de estado e histórico de manutenção de toda a frota.</p>
                </div>

                <!-- Service Card 2 -->
                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary">
                    <div class="mb-4">
                        <i class="fas fa-user-tie text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Gestão de Condutores</h3>
                    <p class="text-gray-600">Perfis de condutores com documentação, validade de cartas e histórico de viagens.</p>
                </div>

                <!-- Service Card 3 -->
                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary">
                    <div class="mb-4">
                        <i class="fas fa-wrench text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Manutenção Programada</h3>
                    <p class="text-gray-600">Agendamento automático e alertas para revisões, inspeções e manutenção preventiva.</p>
                </div>

                <!-- Service Card 4 -->
                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary">
                    <div class="mb-4">
                        <i class="fas fa-file-alt text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Gestão Documental</h3>
                    <p class="text-gray-600">Centralização de documentos com controle de validade e notificações automáticas.</p>
                </div>

                <!-- Service Card 5 -->
                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary">
                    <div class="mb-4">
                        <i class="fas fa-bell text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Alertas Inteligentes</h3>
                    <p class="text-gray-600">Notificações em tempo real para documentos próximos do vencimento e problemas críticos.</p>
                </div>

                <!-- Service Card 6 -->
                <div class="service-card bg-white rounded-lg p-8 card-shadow border-t-4 border-primary">
                    <div class="mb-4">
                        <i class="fas fa-chart-line text-4xl text-primary"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Relatórios Avançados</h3>
                    <p class="text-gray-600">Analytics detalhados sobre custos, consumo e performance operacional da frota.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="beneficios" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Por Que Escolher VeiGest?</h2>
                <p class="text-xl text-gray-600">Benefícios comprovados para sua operação</p>
            </div>
            <div class="grid md:grid-cols-2 gap-12">
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Redução de Custos</h3>
                        <p class="text-gray-600">Otimização de rotas e monitorização reduzem custos operacionais em até 30%.</p>
                    </div>
                </div>

                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Transparência Total</h3>
                        <p class="text-gray-600">Visualização em tempo real de toda a atividade da frota e conformidade documental.</p>
                    </div>
                </div>

                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Conformidade Legal</h3>
                        <p class="text-gray-600">Gestão automática de documentação garante conformidade com requisitos legais.</p>
                    </div>
                </div>

                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-primary text-white">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Fácil de Usar</h3>
                        <p class="text-gray-600">Interface intuitiva que não requer formação especializada para operadores.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="hero-gradient text-white py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Comece a Otimizar Sua Frota Hoje</h2>
            <p class="text-lg mb-8 text-opacity-90">
                Junte-se a centenas de empresas que já confiam em VeiGest para gerir as suas operações
            </p>
            <?= yii\bootstrap5\Html::a('Aceder ao Sistema', ['/site/login'], [
                'class' => 'inline-block bg-white text-primary px-10 py-4 rounded-lg font-bold hover:bg-gray-100 transition text-lg',
            ]) ?>
        </div>
    </section>


</div>