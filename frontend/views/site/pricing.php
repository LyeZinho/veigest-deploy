<?php

use yii\helpers\Html;

$this->title = 'Bem-vindo à VeiGest';
?>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-black">Planos e Preços</h1>
            <p class="text-lg text-opacity-90 text-black">Escolha o plano ideal para a sua empresa</p>
        </div>
    </section>

    <!-- Product Introduction -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Simplifique a Gestão da Sua Frota</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto mb-8">
                    A VeiGest é a plataforma completa para gestão inteligente de frotas. Controle todos os aspetos da sua frota
                    num só lugar: documentos, manutenções, alertas, relatórios e muito mais. Aumente a produtividade,
                    reduza custos e mantenha a sua frota sempre em dia.
                </p>
                <div class="grid md:grid-cols-3 gap-8 mt-12">
                    <div class="text-center">
                        <div class="bg-primary bg-opacity-10 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Conformidade Total</h3>
                        <p class="text-gray-600">Mantenha todos os documentos e certificados em dia automaticamente</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-primary bg-opacity-10 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-line text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Relatórios Inteligentes</h3>
                        <p class="text-gray-600">Insights valiosos para otimizar custos e melhorar a eficiência</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-primary bg-opacity-10 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-2xl text-white"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Alertas Automáticos</h3>
                        <p class="text-gray-600">Nunca mais perca prazos importantes com notificações inteligentes</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Plans -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Starter Plan -->
                <div class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Starter</h3>
                    <p class="text-gray-600 mb-6">Para pequenas frotas</p>
                    <p class="text-4xl font-bold text-primary mb-6">€29<span class="text-sm text-gray-600">/mês</span></p>
                    <ul class="space-y-3 mb-8 text-sm text-gray-600">
                        <li><i class="fas fa-check text-primary mr-2"></i> Até 5 veículos</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Gestão de documentos</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Alertas básicos</li>
                        <li><i class="fas fa-times text-gray-400 mr-2"></i> Relatórios avançados</li>
                    </ul>
                    <button class="w-full bg-gray-200 text-gray-900 py-3 rounded-lg font-bold hover:bg-gray-300 transition">
                        Começar
                    </button>
                </div>

                <!-- Professional Plan -->
                <div class="bg-white rounded-lg shadow-md p-8 border-2 border-primary hover:shadow-lg transition transform scale-105">
                    <div class="text-center mb-4">
                        <span class="inline-block bg-primary text-white px-4 py-1 rounded-full text-sm font-bold">Popular</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Professional</h3>
                    <p class="text-gray-600 mb-6">Para frotas médias</p>
                    <p class="text-4xl font-bold text-primary mb-6">€79<span class="text-sm text-gray-600">/mês</span></p>
                    <ul class="space-y-3 mb-8 text-sm text-gray-600">
                        <li><i class="fas fa-check text-primary mr-2"></i> Até 50 veículos</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Gestão de documentos</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Alertas inteligentes</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Relatórios avançados</li>
                    </ul>
                    <button class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-opacity-90 transition">
                        Começar
                    </button>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-white rounded-lg shadow-md p-8 hover:shadow-lg transition">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Enterprise</h3>
                    <p class="text-gray-600 mb-6">Para grandes frotas</p>
                    <p class="text-4xl font-bold text-primary mb-6">Custom<span class="text-sm text-gray-600">/mês</span></p>
                    <ul class="space-y-3 mb-8 text-sm text-gray-600">
                        <li><i class="fas fa-check text-primary mr-2"></i> Veículos ilimitados</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Todas as funcionalidades</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Suporte prioritário</li>
                        <li><i class="fas fa-check text-primary mr-2"></i> Integração API</li>
                    </ul>
                    <button class="w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-opacity-90 transition">
                        Contactar
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Perguntas Frequentes</h2>
                <p class="text-lg text-gray-600">Esclareça as suas dúvidas sobre os nossos planos</p>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Posso mudar de plano a qualquer momento?</h3>
                    <p class="text-gray-600">Sim! Pode fazer upgrade ou downgrade do seu plano a qualquer momento. As alterações entram em vigor no próximo ciclo de faturação.</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Existe período de teste gratuito?</h3>
                    <p class="text-gray-600">Oferecemos 14 dias de teste gratuito para todos os planos. Não é necessário cartão de crédito para começar.</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Como funciona o suporte técnico?</h3>
                    <p class="text-gray-600">O plano Starter inclui suporte por email. Os planos Professional e Enterprise incluem suporte prioritário por telefone e chat.</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Posso cancelar a qualquer momento?</h3>
                    <p class="text-gray-600">Sim, pode cancelar a sua subscrição a qualquer momento. Não há taxas de cancelamento ou contratos de longo prazo.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Comparison -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Compare os Planos</h2>
                <p class="text-lg text-gray-600">Veja detalhadamente o que cada plano oferece</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full bg-white rounded-lg shadow-md">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-900">Funcionalidades</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-900">Starter</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-900">Professional</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-900">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">Número de veículos</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600">Até 5</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600">Até 50</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-600">Ilimitado</td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">Gestão de documentos</td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">Alertas inteligentes</td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-times text-gray-400"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">Relatórios avançados</td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-times text-gray-400"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">Suporte prioritário</td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-times text-gray-400"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-times text-gray-400"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">Integração API</td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-times text-gray-400"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-times text-gray-400"></i></td>
                            <td class="px-6 py-4 text-center"><i class="fas fa-check text-primary"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">O que os nossos clientes dizem</h2>
                <p class="text-lg text-gray-600">Histórias de sucesso de empresas que confiam na VeiGest</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex text-primary">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"A VeiGest revolucionou a nossa gestão de frota. Conseguimos reduzir custos em 30% e aumentar a produtividade da equipa."</p>
                    <div class="font-bold text-gray-900">Maria Silva</div>
                    <div class="text-sm text-gray-600">Diretora de Operações, Transportes Silva</div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex text-primary">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"Excelente plataforma! O suporte é incrível e as funcionalidades atendem perfeitamente às nossas necessidades."</p>
                    <div class="font-bold text-gray-900">João Santos</div>
                    <div class="text-sm text-gray-600">Gestor de Frota, Logística Express</div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex text-primary">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">"Desde que implementámos a VeiGest, a nossa frota está mais organizada e segura. Recomendo a todas as empresas!"</p>
                    <div class="font-bold text-gray-900">Ana Costa</div>
                    <div class="text-sm text-gray-600">Coordenadora de Frotas, Distribuidora Nacional</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-primary to-blue-500 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4">Pronto para começar?</h2>
            <p class="text-lg text-opacity-90 mb-8">Junte-se a centenas de empresas que já confiam na VeiGest para gerir as suas frotas</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/site/signup" class="bg-white text-primary px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition border-primary">
                    Começar Teste Gratuito
                </a>
                <a href="/site/contact" class="border-2 border-white text-white px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition">
                    Falar com Especialista
                </a>
            </div>
            <p class="text-sm text-opacity-75 mt-4">Sem compromisso • 14 dias grátis • Cancele quando quiser</p>
        </div>
    </section>


</body>
</html>
