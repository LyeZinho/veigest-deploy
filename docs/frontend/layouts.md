# 🖼️ Layouts

## Visão Geral

Os layouts definem a estrutura HTML base das páginas e estão em `frontend/views/layouts/`.

## Layouts Disponíveis

| Layout | Descrição | Uso |
|--------|-----------|-----|
| `main.php` | Layout público | Homepage, páginas de marketing |
| `dashboard.php` | Layout autenticado | Dashboard, gestão |
| `login.php` | Layout minimalista | Login, registo, reset password |

---

## Layout Main (Público)

### Estrutura (`main.php`)

```php
<?php
/**
 * @var yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - VeiGest</title>
    <?php $this->head() ?>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50">
<?php $this->beginBody() ?>

    <!-- Header/Navbar -->
    <header class="bg-white shadow-sm fixed w-full top-0 z-50">
        <nav class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="<?= Yii::$app->homeUrl ?>" class="flex items-center gap-2">
                    <img src="/images/logo.svg" alt="VeiGest" class="h-8">
                    <span class="text-xl font-bold text-blue-600">VeiGest</span>
                </a>
                
                <!-- Menu Desktop -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="<?= Yii::$app->urlManager->createUrl(['site/services']) ?>" 
                       class="text-gray-600 hover:text-blue-600">
                        Serviços
                    </a>
                    <a href="<?= Yii::$app->urlManager->createUrl(['site/pricing']) ?>" 
                       class="text-gray-600 hover:text-blue-600">
                        Preços
                    </a>
                    <a href="<?= Yii::$app->urlManager->createUrl(['site/about']) ?>" 
                       class="text-gray-600 hover:text-blue-600">
                        Sobre
                    </a>
                    <a href="<?= Yii::$app->urlManager->createUrl(['site/contact']) ?>" 
                       class="text-gray-600 hover:text-blue-600">
                        Contacto
                    </a>
                </div>
                
                <!-- Auth Buttons -->
                <div class="flex items-center gap-3">
                    <?php if (Yii::$app->user->isGuest): ?>
                        <a href="<?= Yii::$app->urlManager->createUrl(['site/login']) ?>" 
                           class="text-gray-600 hover:text-blue-600">
                            Entrar
                        </a>
                        <a href="<?= Yii::$app->urlManager->createUrl(['site/signup']) ?>" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Criar Conta
                        </a>
                    <?php else: ?>
                        <a href="<?= Yii::$app->urlManager->createUrl(['dashboard/index']) ?>" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Dashboard
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Menu Button -->
                <button class="md:hidden" id="mobile-menu-btn">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main class="pt-16">
        <?= Alert::widget() ?>
        <?= $content ?>
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12 mt-auto">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- About -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">VeiGest</h3>
                    <p class="text-gray-400 text-sm">
                        Sistema completo de gestão de frotas para empresas de todos os tamanhos.
                    </p>
                </div>
                
                <!-- Links Rápidos -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Links</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white">Funcionalidades</a></li>
                        <li><a href="#" class="hover:text-white">Preços</a></li>
                        <li><a href="#" class="hover:text-white">Documentação</a></li>
                    </ul>
                </div>
                
                <!-- Contacto -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>suporte@veigest.com</li>
                        <li>+351 123 456 789</li>
                    </ul>
                </div>
                
                <!-- Social -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Redes Sociais</h3>
                    <div class="flex gap-4">
                        <!-- Icons -->
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-400">
                &copy; <?= date('Y') ?> VeiGest. Todos os direitos reservados.
            </div>
        </div>
    </footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

---

## Layout Dashboard (Autenticado)

### Estrutura (`dashboard.php`)

```php
<?php
/**
 * @var yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;
use yii\helpers\Url;
use frontend\assets\DashboardAsset;
use common\widgets\Alert;

DashboardAsset::register($this);

$user = Yii::$app->user->identity;
$currentRoute = Yii::$app->controller->route;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - VeiGest</title>
    <?php $this->head() ?>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
<?php $this->beginBody() ?>

<div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 text-white fixed h-full z-40" id="sidebar">
        <!-- Logo -->
        <div class="p-4 border-b border-gray-700">
            <a href="<?= Url::to(['dashboard/index']) ?>" class="flex items-center gap-2">
                <img src="/images/logo-white.svg" alt="VeiGest" class="h-8">
                <span class="text-xl font-bold">VeiGest</span>
            </a>
        </div>
        
        <!-- Perfil do Utilizador -->
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                    <?= strtoupper(substr($user->username, 0, 1)) ?>
                </div>
                <div>
                    <p class="font-medium"><?= Html::encode($user->username) ?></p>
                    <p class="text-sm text-gray-400"><?= ucfirst($user->role) ?></p>
                </div>
            </div>
        </div>
        
        <!-- Menu de Navegação -->
        <nav class="p-4">
            <ul class="space-y-2">
                <!-- Dashboard -->
                <?= $this->render('_menu-item', [
                    'icon' => 'dashboard',
                    'label' => 'Dashboard',
                    'url' => ['dashboard/index'],
                    'active' => $currentRoute === 'dashboard/index',
                ]) ?>
                
                <!-- Veículos -->
                <?= $this->render('_menu-item', [
                    'icon' => 'vehicle',
                    'label' => 'Veículos',
                    'url' => ['dashboard/vehicles'],
                    'active' => strpos($currentRoute, 'vehicle') !== false,
                ]) ?>
                
                <!-- Manutenção -->
                <?= $this->render('_menu-item', [
                    'icon' => 'maintenance',
                    'label' => 'Manutenção',
                    'url' => ['dashboard/maintenance'],
                    'active' => strpos($currentRoute, 'maintenance') !== false,
                ]) ?>
                
                <!-- Condutores -->
                <?= $this->render('_menu-item', [
                    'icon' => 'driver',
                    'label' => 'Condutores',
                    'url' => ['dashboard/drivers'],
                    'active' => strpos($currentRoute, 'driver') !== false,
                ]) ?>
                
                <!-- Documentos -->
                <?= $this->render('_menu-item', [
                    'icon' => 'document',
                    'label' => 'Documentos',
                    'url' => ['document/index'],
                    'active' => strpos($currentRoute, 'document') !== false,
                ]) ?>
                
                <!-- Relatórios -->
                <?= $this->render('_menu-item', [
                    'icon' => 'report',
                    'label' => 'Relatórios',
                    'url' => ['report/index'],
                    'active' => strpos($currentRoute, 'report') !== false,
                ]) ?>
                
                <!-- Alertas -->
                <?= $this->render('_menu-item', [
                    'icon' => 'alert',
                    'label' => 'Alertas',
                    'url' => ['dashboard/alerts'],
                    'active' => strpos($currentRoute, 'alert') !== false,
                    'badge' => Yii::$app->user->identity->getActiveAlertsCount(),
                ]) ?>
                
                <?php if (in_array($user->role, ['admin', 'gestor'])): ?>
                <li class="pt-4 mt-4 border-t border-gray-700">
                    <span class="text-xs text-gray-500 uppercase">Administração</span>
                </li>
                
                <!-- Utilizadores -->
                <?= $this->render('_menu-item', [
                    'icon' => 'users',
                    'label' => 'Utilizadores',
                    'url' => ['user/index'],
                    'active' => strpos($currentRoute, 'user') !== false,
                ]) ?>
                
                <!-- Configurações -->
                <?= $this->render('_menu-item', [
                    'icon' => 'settings',
                    'label' => 'Configurações',
                    'url' => ['settings/index'],
                    'active' => strpos($currentRoute, 'settings') !== false,
                ]) ?>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content Area -->
    <div class="flex-1 ml-64">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-6 sticky top-0 z-30">
            <!-- Mobile Menu Toggle -->
            <button class="lg:hidden" id="sidebar-toggle">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <!-- Breadcrumbs -->
            <nav class="flex items-center text-sm">
                <a href="<?= Url::to(['dashboard/index']) ?>" class="text-gray-500 hover:text-blue-600">
                    Dashboard
                </a>
                <?php if (!empty($this->params['breadcrumbs'])): ?>
                    <?php foreach ($this->params['breadcrumbs'] as $crumb): ?>
                        <span class="mx-2 text-gray-400">/</span>
                        <?php if (is_array($crumb)): ?>
                            <a href="<?= Url::to($crumb['url']) ?>" class="text-gray-500 hover:text-blue-600">
                                <?= Html::encode($crumb['label']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-gray-800"><?= Html::encode($crumb) ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>
            
            <!-- Right Side -->
            <div class="flex items-center gap-4">
                <!-- Notificações -->
                <div class="relative">
                    <button class="relative p-2 text-gray-500 hover:text-gray-700" id="notifications-btn">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <?php if ($alertCount = $user->getActiveAlertsCount()): ?>
                        <span class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white text-xs 
                                     rounded-full flex items-center justify-center">
                            <?= $alertCount ?>
                        </span>
                        <?php endif; ?>
                    </button>
                </div>
                
                <!-- User Menu -->
                <div class="relative" id="user-menu">
                    <button class="flex items-center gap-2 text-gray-700 hover:text-gray-900">
                        <span class="hidden sm:block"><?= Html::encode($user->username) ?></span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Dropdown -->
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 hidden" 
                         id="user-dropdown">
                        <a href="<?= Url::to(['user/profile']) ?>" 
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            Meu Perfil
                        </a>
                        <a href="<?= Url::to(['user/settings']) ?>" 
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            Configurações
                        </a>
                        <hr class="my-2">
                        <?= Html::beginForm(['site/logout'], 'post') ?>
                            <?= Html::submitButton('Sair', [
                                'class' => 'w-full text-left px-4 py-2 text-red-600 hover:bg-red-50'
                            ]) ?>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-6">
            <?= Alert::widget() ?>
            <?= $content ?>
        </main>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

### Menu Item Partial (`_menu-item.php`)

```php
<?php
/**
 * @var string $icon
 * @var string $label
 * @var array $url
 * @var bool $active
 * @var int|null $badge
 */

use yii\helpers\Html;
use yii\helpers\Url;

$icons = [
    'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    'vehicle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>',
    // ... mais ícones
];

$activeClass = $active ? 'bg-gray-700' : 'hover:bg-gray-700';
?>

<li>
    <a href="<?= Url::to($url) ?>" 
       class="flex items-center gap-3 px-3 py-2 rounded-lg <?= $activeClass ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <?= $icons[$icon] ?? '' ?>
        </svg>
        <span><?= Html::encode($label) ?></span>
        <?php if (!empty($badge)): ?>
        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
            <?= $badge ?>
        </span>
        <?php endif; ?>
    </a>
</li>
```

---

## Layout Login (Minimalista)

### Estrutura (`login.php`)

```php
<?php
/**
 * @var yii\web\View $this
 * @var string $content
 */

use yii\helpers\Html;
use frontend\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?> - VeiGest</title>
    <?php $this->head() ?>
    
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700">
<?php $this->beginBody() ?>

    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full">
            <!-- Pattern SVG -->
        </svg>
    </div>
    
    <!-- Content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <?= $content ?>
    </div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

---

## Trocar Layout no Controller

```php
class SiteController extends Controller
{
    public $layout = 'main';  // Layout padrão
    
    public function actionLogin()
    {
        $this->layout = 'login';  // Trocar para este action
        return $this->render('login');
    }
}
```

---

## Blocos de Conteúdo

### Definir Blocos nas Views

```php
// Na view
<?php $this->beginBlock('sidebar'); ?>
    <div class="sidebar-content">
        <!-- Conteúdo específico -->
    </div>
<?php $this->endBlock(); ?>
```

### Usar Blocos no Layout

```php
// No layout
<?php if (isset($this->blocks['sidebar'])): ?>
    <aside class="sidebar">
        <?= $this->blocks['sidebar'] ?>
    </aside>
<?php endif; ?>
```

---

## Registar CSS/JS no Layout

```php
// Registar CSS
$this->registerCssFile('/css/custom.css', ['depends' => [\yii\web\YiiAsset::class]]);

// CSS inline
$this->registerCss('.custom { color: red; }');

// Registar JS
$this->registerJsFile('/js/custom.js', ['depends' => [\yii\web\JqueryAsset::class]]);

// JS inline
$this->registerJs('console.log("Loaded");', \yii\web\View::POS_READY);
```

---

## Próximos Passos

- [Assets](assets.md)
- [Controllers](controllers.md)
