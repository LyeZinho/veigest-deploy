<?php

use yii\helpers\Html;

$this->title = 'Bem-vindo à VeiGest';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segurança - VeiGest</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #09BC8A;
            --color-onyx: #3C3C3C;
        }
        .text-primary { color: var(--color-primary); }
        .bg-primary { background-color: var(--color-primary); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <img src="/images/veigest-logo.png" alt="VeiGest" class="h-10 w-10">
                    <span class="text-xl font-bold text-primary">VeiGest</span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="index.html" class="text-gray-700 hover:text-primary transition">Início</a>
                    <a href="pricing.html" class="text-gray-700 hover:text-primary transition">Preços</a>
                    <a href="security.html" class="text-gray-700 hover:text-primary transition font-bold">Segurança</a>
                    <a href="support.html" class="text-gray-700 hover:text-primary transition">Suporte</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.html" class="text-gray-700 hover:text-primary font-medium">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Segurança em Primeiro Lugar</h1>
            <p class="text-lg text-opacity-90">Protegemos os seus dados com as melhores práticas de segurança</p>
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
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="text-white font-bold mb-4">VeiGest</h4>
                    <p class="text-sm">Plataforma inteligente de gestão de frotas.</p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">Recursos</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="documentation.html" class="hover:text-primary">Documentação</a></li>
                        <li><a href="blog.html" class="hover:text-primary">Blog</a></li>
                        <li><a href="support.html" class="hover:text-primary">Suporte</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">Produto</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="services.html" class="hover:text-primary">Serviços</a></li>
                        <li><a href="pricing.html" class="hover:text-primary">Preços</a></li>
                        <li><a href="security.html" class="hover:text-primary">Segurança</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">Contacto</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2"><i class="fas fa-envelope"></i> info@veigest.com</li>
                        <li class="flex items-center gap-2"><i class="fas fa-phone"></i> +351 21 0000 000</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-8 text-center text-sm">
                <p>&copy; 2025 VeiGest. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>
