<?php

use yii\helpers\Html;

$this->title = 'Bem-vindo à VeiGest';
?>

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
                    <a href="documentation.html" class="text-gray-700 hover:text-primary transition">Documentação</a>
                    <a href="blog.html" class="text-gray-700 hover:text-primary transition font-bold">Blog</a>
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
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Blog VeiGest</h1>
            <p class="text-lg text-opacity-90">Artigos e dicas para otimizar a sua gestão de frotas</p>
        </div>
    </section>

    <!-- Blog Posts -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Post 1 -->
                <article class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                    <div class="h-40 bg-gradient-to-r from-primary to-blue-500"></div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500">Janeiro 15, 2025</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">Dicas para Reduzir Custos Operacionais</h3>
                        <p class="text-gray-600 mt-3 text-sm">Estratégias práticas para otimizar despesas e melhorar a rentabilidade da sua frota.</p>
                        <a href="#" class="text-primary hover:underline font-medium mt-4 inline-block">Ler Mais →</a>
                    </div>
                </article>

                <!-- Post 2 -->
                <article class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                    <div class="h-40 bg-gradient-to-r from-blue-500 to-purple-500"></div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500">Janeiro 10, 2025</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">Conformidade Legal em Frotas</h3>
                        <p class="text-gray-600 mt-3 text-sm">Tudo o que precisa saber sobre regulamentos e documentação obrigatória.</p>
                        <a href="#" class="text-primary hover:underline font-medium mt-4 inline-block">Ler Mais →</a>
                    </div>
                </article>

                <!-- Post 3 -->
                <article class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden">
                    <div class="h-40 bg-gradient-to-r from-purple-500 to-pink-500"></div>
                    <div class="p-6">
                        <p class="text-sm text-gray-500">Janeiro 5, 2025</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">Tecnologia e Manutenção Preventiva</h3>
                        <p class="text-gray-600 mt-3 text-sm">Como a tecnologia ajuda a antecipar problemas de manutenção.</p>
                        <a href="#" class="text-primary hover:underline font-medium mt-4 inline-block">Ler Mais →</a>
                    </div>
                </article>
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
