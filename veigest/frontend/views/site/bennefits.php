<?php

use yii\helpers\Html;

$this->title = 'Bem-vindo à VeiGest';
?>
<body class="bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-black">Os Nossos Serviços</h1>
            <p class="text-lg text-opacity-90 text-black">Soluções completas para gestão eficiente de frotas</p>
        </div>
    </section>

    <!-- Services -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 mb-16">
                <!-- Service 1 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-car-side text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Gestão de Veículos</h3>
                        <p class="text-gray-600">Cadastro completo, rastreamento de estado, histórico de manutenção e documentação centralizada para toda a frota.</p>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Gestão de Condutores</h3>
                        <p class="text-gray-600">Perfis de condutores com documentação, validade de cartas, histórico de viagens e análise de desempenho.</p>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-tools text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Manutenção Programada</h3>
                        <p class="text-gray-600">Agendamento automático de revisões, inspeções e manutenção preventiva com alertas e notificações.</p>
                    </div>
                </div>

                <!-- Service 4 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-chart-bar text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Relatórios Avançados</h3>
                        <p class="text-gray-600">Analytics detalhados sobre custos operacionais, consumo de combustível e performance da frota.</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="bg-gray-50 rounded-lg p-8 mb-16">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Impacto na Sua Empresa</h3>
                    <p class="text-gray-600">Resultados comprovados em empresas que utilizam a VeiGest</p>
                </div>
                <div class="grid md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">30%</div>
                        <p class="text-gray-600">Redução de custos operacionais</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">95%</div>
                        <p class="text-gray-600">Documentos sempre em dia</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">50%</div>
                        <p class="text-gray-600">Menos tempo em tarefas administrativas</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">24/7</div>
                        <p class="text-gray-600">Monitorização contínua</p>
                    </div>
                </div>
            </div>

            <!-- Additional Features -->
            <div class="grid md:grid-cols-2 gap-12 mb-16">
                <!-- Feature 5 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-bell text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Alertas Inteligentes</h3>
                        <p class="text-gray-600">Notificações automáticas sobre vencimentos de documentos, revisões pendentes e anomalias na frota.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-mobile-alt text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Aplicação Móvel</h3>
                        <p class="text-gray-600">Acesso completo via smartphone para condutores e gestores, com funcionalidades offline.</p>
                    </div>
                </div>

                <!-- Feature 7 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-cloud text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Cópia de Segurança Automática</h3>
                        <p class="text-gray-600">Sincronização automática de dados na nuvem com cópia de segurança redundante e recuperação de desastres.</p>
                    </div>
                </div>

                <!-- Feature 8 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Segurança Avançada</h3>
                        <p class="text-gray-600">Encriptação de dados, controlo de acesso baseado em funções e conformidade com RGPD.</p>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white border border-gray-200 rounded-lg p-8 mb-16">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Casos de Uso</h3>
                    <p class="text-gray-600">A VeiGest adapta-se às necessidades de diferentes tipos de empresas</p>
                </div>
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="text-center p-6 border border-gray-100 rounded-lg">
                        <i class="fas fa-truck text-3xl text-primary mb-4"></i>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Transportadoras</h4>
                        <p class="text-gray-600 text-sm">Gestão de frotas de camiões, controlo de rotas e documentação obrigatória do transporte rodoviário.</p>
                    </div>
                    <div class="text-center p-6 border border-gray-100 rounded-lg">
                        <i class="fas fa-building text-3xl text-primary mb-4"></i>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Empresas de Construção</h4>
                        <p class="text-gray-600 text-sm">Controle de equipamentos pesados, manutenção preventiva e gestão de operadores certificados.</p>
                    </div>
                    <div class="text-center p-6 border border-gray-100 rounded-lg">
                        <i class="fas fa-shopping-cart text-3xl text-primary mb-4"></i>
                        <h4 class="text-lg font-bold text-gray-900 mb-2">Distribuidoras</h4>
                        <p class="text-gray-600 text-sm">Otimização de rotas de entrega, controle de combustível e gestão de múltiplos veículos.</p>
                    </div>
                </div>
            </div>

            <!-- Integrations -->
            <div class="bg-gray-50 rounded-lg p-8 mb-16">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Integrações Disponíveis</h3>
                    <p class="text-gray-600">Conecte a VeiGest com seus sistemas existentes</p>
                </div>
                <div class="grid md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <i class="fab fa-google text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Google Maps</p>
                        <p class="text-sm text-gray-600">Roteirização inteligente</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-file-invoice-dollar text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Sistemas Contabilísticos</p>
                        <p class="text-sm text-gray-600">Integração financeira</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-gas-pump text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Postos de Combustível</p>
                        <p class="text-sm text-gray-600">Controle de abastecimento</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-tools text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Oficinas</p>
                        <p class="text-sm text-gray-600">Gestão de reparos</p>
                    </div>
                </div>
            </div>

            <!-- Compliance & Security -->
            <div class="bg-white border border-gray-200 rounded-lg p-8 mb-16">
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Conformidade e Segurança</h3>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Conformidade com legislação de transporte (Código da Estrada, IMT)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Proteção de dados pessoais (conformidade com RGPD)</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Encriptação ponto-a-ponto de dados sensíveis</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Auditoria completa de todas as ações do sistema</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Suporte e Capacitação</h3>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Suporte técnico 24/7 via telefone e chat</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Formação completa da equipa</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Documentação técnica detalhada</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-primary mt-1"></i>
                                <span>Atualizações automáticas do sistema</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center">
                <a href="login.html" class="inline-block bg-primary text-white px-10 py-4 rounded-lg font-bold hover:bg-opacity-90 transition text-lg">
                    Começar Agora
                </a>
            </div>
        </div>
    </section>
</body>
</html>
