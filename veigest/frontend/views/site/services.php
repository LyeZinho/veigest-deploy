<?php

use yii\helpers\Html;

$this->title = 'Bem-vindo à VeiGest';
?>
<div class="bg-gray-50">

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-black">Segurança em Primeiro Lugar</h1>
            <p class="text-lg text-opacity-90 text-black">Protegemos os seus dados com as melhores práticas de segurança</p>
        </div>
    </section>

    <!-- Security Features -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Feature 1 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-lock text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Encriptação de Dados</h3>
                        <p class="text-gray-600">Todos os dados são encriptados em trânsito e em repouso usando protocolos SSL/TLS de nível bancário.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-user-shield text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Controlo de Acesso</h3>
                        <p class="text-gray-600">Sistema RBAC robusto que garante que cada utilizador tenha apenas acesso aos dados que precisa.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-server text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Infraestrutura Segura</h3>
                        <p class="text-gray-600">Hospedagem em data centers certificados com backups automáticos e redundância de sistemas.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="flex gap-6">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Conformidade</h3>
                        <p class="text-gray-600">Conformidade total com RGPD, normas de segurança internacionais e regulamentos de proteção de dados.</p>
                    </div>
                </div>
            </div>

            <!-- Certifications -->
            <div class="mt-16 pt-16 border-t border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Certificações e Padrões</h2>
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <div class="bg-white rounded-lg p-8 shadow-md">
                        <i class="fas fa-certificate text-4xl text-primary mb-4"></i>
                        <p class="font-bold text-gray-900">ISO 27001</p>
                        <p class="text-sm text-gray-600 mt-2">Gestão de Segurança da Informação</p>
                    </div>
                    <div class="bg-white rounded-lg p-8 shadow-md">
                        <i class="fas fa-certificate text-4xl text-primary mb-4"></i>
                        <p class="font-bold text-gray-900">RGPD</p>
                        <p class="text-sm text-gray-600 mt-2">Proteção de Dados Pessoais</p>
                    </div>
                    <div class="bg-white rounded-lg p-8 shadow-md">
                        <i class="fas fa-certificate text-4xl text-primary mb-4"></i>
                        <p class="font-bold text-gray-900">SOC 2</p>
                        <p class="text-sm text-gray-600 mt-2">Conformidade de Segurança</p>
                    </div>
                    <div class="bg-white rounded-lg p-8 shadow-md">
                        <i class="fas fa-certificate text-4xl text-primary mb-4"></i>
                        <p class="font-bold text-gray-900">OWASP</p>
                        <p class="text-sm text-gray-600 mt-2">Segurança de Aplicações</p>
                    </div>
                </div>
            </div>

            <!-- Additional Security Features -->
            <div class="mt-16 pt-16 border-t border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Recursos de Segurança Avançados</h2>
                <div class="grid md:grid-cols-2 gap-12">
                    <!-- Feature 5 -->
                    <div class="flex gap-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                                <i class="fas fa-eye text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Monitorização 24/7</h3>
                            <p class="text-gray-600">Sistema de monitorização contínuo que deteta e responde a ameaças em tempo real, com alertas automáticos para a equipa de segurança.</p>
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="flex gap-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                                <i class="fas fa-key text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Autenticação Multifator</h3>
                            <p class="text-gray-600">MFA obrigatório para todos os utilizadores, com suporte a aplicações de autenticação, SMS e tokens de hardware para máxima segurança.</p>
                        </div>
                    </div>

                    <!-- Feature 7 -->
                    <div class="flex gap-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                                <i class="fas fa-database text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Cópia de Segurança</h3>
                            <p class="text-gray-600">Cópias de segurança automáticas encriptadas armazenadas em múltiplas localizações geográficas, com testes regulares de recuperação.</p>
                        </div>
                    </div>

                    <!-- Feature 8 -->
                    <div class="flex gap-6">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-16 w-16 rounded-md bg-primary text-white">
                                <i class="fas fa-user-graduate text-2xl"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Formação Contínua</h3>
                            <p class="text-gray-600">Programas de sensibilização em segurança para utilizadores, com formações regulares sobre melhores práticas e reconhecimento de ameaças.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Statistics -->
            <div class="mt-16 pt-16 border-t border-gray-200 bg-gray-50 rounded-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Nossa Performance de Segurança</h2>
                <div class="grid md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">99.9%</div>
                        <p class="text-gray-600">Uptime do Sistema</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">0</div>
                        <p class="text-gray-600">Incidentes de Segurança em 2024</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">< 1min</div>
                        <p class="text-gray-600">Tempo Médio de Detecção</p>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-primary mb-2">24/7</div>
                        <p class="text-gray-600">Monitoramento de Segurança</p>
                    </div>
                </div>
            </div>

            <!-- Compliance & Audit -->
            <div class="mt-16 pt-16 border-t border-gray-200">
                <div class="grid md:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">Auditoria e Conformidade</h2>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-primary text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Auditorias Regulares</h3>
                                    <p class="text-gray-600">Auditorias de segurança realizadas trimestralmente por terceiros certificados, com relatórios detalhados disponíveis.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-primary text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Registos de Auditoria</h3>
                                    <p class="text-gray-600">Registo completo de todas as ações do sistema, com retenção de registos por 7 anos conforme requisitos legais.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-primary text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Relatórios de Conformidade</h3>
                                    <p class="text-gray-600">Relatórios automáticos demonstrando conformidade com todas as regulamentações aplicáveis ao setor de transporte.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">Suporte de Segurança</h2>
                        <div class="space-y-6">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-headset text-primary text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Centro de Segurança 24/7</h3>
                                    <p class="text-gray-600">Equipa dedicada de especialistas em segurança disponível 24 horas por dia, 7 dias por semana.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-shield-virus text-primary text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Resposta a Incidentes</h3>
                                    <p class="text-gray-600">Protocolos estabelecidos para resposta rápida a qualquer incidente de segurança, minimizando impactos.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-book text-primary text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-2">Documentação Técnica</h3>
                                    <p class="text-gray-600">Documentação completa sobre arquitetura de segurança, políticas e procedimentos para transparência total.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Partners -->
            <div class="mt-16 pt-16 border-t border-gray-200 bg-gray-50 rounded-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Parceiros de Segurança</h2>
                <div class="grid md:grid-cols-5 gap-6 text-center">
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <i class="fab fa-aws text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Amazon Web Services</p>
                        <p class="text-sm text-gray-600">Infraestrutura Cloud</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <i class="fab fa-microsoft text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Microsoft Azure</p>
                        <p class="text-sm text-gray-600">Segurança Empresarial</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <i class="fas fa-lock text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">CrowdStrike</p>
                        <p class="text-sm text-gray-600">Proteção Avançada</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <i class="fas fa-shield-alt text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Palo Alto</p>
                        <p class="text-sm text-gray-600">Firewall Next-Gen</p>
                    </div>
                    <div class="bg-white rounded-lg p-6 shadow-md">
                        <i class="fas fa-user-secret text-3xl text-primary mb-2"></i>
                        <p class="font-medium text-gray-900">Okta</p>
                        <p class="text-sm text-gray-600">Gestão de Identidade</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
