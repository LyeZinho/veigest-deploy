# 📦 Assets (CSS, JS, Imagens)

## Visão Geral

O Yii2 utiliza **Asset Bundles** para gerir CSS, JavaScript e outros ficheiros estáticos. Isso garante que as dependências são carregadas na ordem correta e apenas quando necessárias.

## Estrutura de Diretórios

```
frontend/
├── assets/                    # Asset Bundles (classes PHP)
│   ├── AppAsset.php          # Bundle principal
│   └── DashboardAsset.php    # Bundle do dashboard
├── web/                       # Ficheiros públicos
│   ├── css/
│   │   ├── site.css          # Estilos gerais
│   │   └── dashboard.css     # Estilos dashboard
│   ├── js/
│   │   ├── main.js           # Scripts gerais
│   │   └── charts.js         # Scripts de gráficos
│   └── images/
│       ├── logo.svg
│       └── favicon.ico
```

---

## Asset Bundles

### AppAsset (Bundle Principal)

```php
<?php
// frontend/assets/AppAsset.php

namespace frontend\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    // Diretório base dos ficheiros
    public $basePath = '@webroot';
    
    // URL base para os ficheiros
    public $baseUrl = '@web';
    
    // Ficheiros CSS a incluir
    public $css = [
        'css/site.css',
    ];
    
    // Ficheiros JS a incluir
    public $js = [
        'js/main.js',
    ];
    
    // Dependências (carregadas primeiro)
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
```

### DashboardAsset

```php
<?php
// frontend/assets/DashboardAsset.php

namespace frontend\assets;

use yii\web\AssetBundle;

class DashboardAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    
    public $css = [
        'css/dashboard.css',
    ];
    
    public $js = [
        'js/dashboard.js',
        'js/charts.js',
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
        // Chart.js externo
        ChartJsAsset::class,
    ];
}
```

### Chart.js Asset (Externo)

```php
<?php
// frontend/assets/ChartJsAsset.php

namespace frontend\assets;

use yii\web\AssetBundle;

class ChartJsAsset extends AssetBundle
{
    public $sourcePath = null;
    
    // CDN externo
    public $js = [
        'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
    ];
    
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD,
    ];
}
```

---

## Registar Assets

### No Layout

```php
<?php
use frontend\assets\AppAsset;

// Registar o bundle
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
...
```

### Em Views Específicas

```php
<?php
// Apenas nesta view
use frontend\assets\DashboardAsset;
DashboardAsset::register($this);
?>
```

---

## CSS Personalizado

### Ficheiro CSS Principal (`site.css`)

```css
/* frontend/web/css/site.css */

/* === Reset & Base === */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    line-height: 1.6;
    color: #374151;
}

/* === Utilitários === */
.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 1rem;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

/* === Componentes === */

/* Botões */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 0.5rem;
    transition: all 0.2s;
    cursor: pointer;
}

.btn-primary {
    background-color: #3B82F6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563EB;
}

.btn-secondary {
    background-color: #6B7280;
    color: white;
}

.btn-danger {
    background-color: #EF4444;
    color: white;
}

.btn-outline {
    background-color: transparent;
    border: 1px solid #D1D5DB;
    color: #374151;
}

.btn-outline:hover {
    background-color: #F3F4F6;
}

/* Cards */
.card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #E5E7EB;
    font-weight: 600;
}

.card-body {
    padding: 1.5rem;
}

.card-footer {
    padding: 1rem 1.5rem;
    background: #F9FAFB;
    border-top: 1px solid #E5E7EB;
}

/* Badges */
.badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 9999px;
}

.badge-success {
    background: #D1FAE5;
    color: #065F46;
}

.badge-warning {
    background: #FEF3C7;
    color: #92400E;
}

.badge-danger {
    background: #FEE2E2;
    color: #991B1B;
}

.badge-info {
    background: #DBEAFE;
    color: #1E40AF;
}

/* Formulários */
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.5rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3B82F6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control.is-invalid {
    border-color: #EF4444;
}

.invalid-feedback {
    color: #EF4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Tabelas */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #E5E7EB;
}

.table th {
    font-weight: 600;
    color: #6B7280;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table tbody tr:hover {
    background: #F9FAFB;
}

/* Alertas */
.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    border-left: 4px solid;
}

.alert-success {
    background: #D1FAE5;
    border-color: #10B981;
    color: #065F46;
}

.alert-warning {
    background: #FEF3C7;
    border-color: #F59E0B;
    color: #92400E;
}

.alert-danger {
    background: #FEE2E2;
    border-color: #EF4444;
    color: #991B1B;
}

.alert-info {
    background: #DBEAFE;
    border-color: #3B82F6;
    color: #1E40AF;
}
```

### Dashboard CSS (`dashboard.css`)

```css
/* frontend/web/css/dashboard.css */

/* === Layout Dashboard === */
.dashboard-sidebar {
    width: 256px;
    background: #1F2937;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
}

.dashboard-content {
    margin-left: 256px;
    min-height: 100vh;
    background: #F3F4F6;
}

/* Menu Items */
.menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #9CA3AF;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.menu-item:hover {
    background: #374151;
    color: white;
}

.menu-item.active {
    background: #3B82F6;
    color: white;
}

/* KPI Cards */
.kpi-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.kpi-value {
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
}

.kpi-label {
    color: #6B7280;
    font-size: 0.875rem;
}

.kpi-trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
}

.kpi-trend.up {
    color: #10B981;
}

.kpi-trend.down {
    color: #EF4444;
}

/* Gráficos */
.chart-container {
    position: relative;
    height: 300px;
}

/* Status Indicators */
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.status-active { background: #10B981; }
.status-maintenance { background: #F59E0B; }
.status-inactive { background: #6B7280; }

/* Responsive */
@media (max-width: 1024px) {
    .dashboard-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s;
        z-index: 50;
    }
    
    .dashboard-sidebar.open {
        transform: translateX(0);
    }
    
    .dashboard-content {
        margin-left: 0;
    }
}
```

---

## JavaScript

### Main.js

```javascript
// frontend/web/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (menuBtn && mobileMenu) {
        menuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // Flash messages auto-hide
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Utilitários globais
const VeiGest = {
    // Formatar moeda
    formatCurrency: function(value, locale = 'pt-PT', currency = 'EUR') {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency
        }).format(value);
    },
    
    // Formatar data
    formatDate: function(date, locale = 'pt-PT') {
        return new Intl.DateTimeFormat(locale, {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }).format(new Date(date));
    },
    
    // Confirmar ação
    confirm: function(message, callback) {
        if (window.confirm(message)) {
            callback();
        }
    },
    
    // Toast notification
    toast: function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 
            ${type === 'success' ? 'bg-green-500' : 
              type === 'error' ? 'bg-red-500' : 'bg-blue-500'} text-white`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.transition = 'opacity 0.5s';
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
};
```

### Dashboard.js

```javascript
// frontend/web/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle (mobile)
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
    }
    
    // User dropdown
    const userMenu = document.getElementById('user-menu');
    const userDropdown = document.getElementById('user-dropdown');
    
    if (userMenu && userDropdown) {
        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function() {
            userDropdown.classList.add('hidden');
        });
    }
    
    // Notifications
    const notificationsBtn = document.getElementById('notifications-btn');
    if (notificationsBtn) {
        notificationsBtn.addEventListener('click', function() {
            // Abrir painel de notificações
        });
    }
});
```

### Charts.js

```javascript
// frontend/web/js/charts.js

const ChartDefaults = {
    // Cores padrão
    colors: {
        primary: '#3B82F6',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        gray: '#6B7280',
    },
    
    // Opções comuns
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
        },
    },
    
    // Criar gráfico de barras
    createBarChart: function(canvasId, labels, data, label = 'Dados') {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    backgroundColor: this.colors.primary,
                    borderRadius: 4,
                }]
            },
            options: {
                ...this.options,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    },
    
    // Criar gráfico de linha
    createLineChart: function(canvasId, labels, datasets) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets.map((ds, i) => ({
                    ...ds,
                    borderColor: Object.values(this.colors)[i],
                    backgroundColor: 'transparent',
                    tension: 0.4,
                }))
            },
            options: this.options
        });
    },
    
    // Criar gráfico de rosca
    createDoughnutChart: function(canvasId, labels, data) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: Object.values(this.colors),
                }]
            },
            options: {
                ...this.options,
                cutout: '60%',
            }
        });
    }
};
```

---

## Publicar Assets

Assets são publicados automaticamente quando registados. Para forçar republicação (após alterações):

```bash
# Limpar cache de assets
rm -rf frontend/web/assets/*

# Ou via Yii console
php yii asset/compress config/assets-compress.php config/assets-compress-prod.php
```

---

## Boas Práticas

### 1. Organizar por Funcionalidade

```
web/
├── css/
│   ├── base/         # Reset, variáveis, tipografia
│   ├── components/   # Botões, cards, modals
│   ├── layouts/      # Layouts específicos
│   └── pages/        # Estilos por página
├── js/
│   ├── utils/        # Utilitários
│   ├── components/   # Componentes JS
│   └── pages/        # Scripts por página
```

### 2. Usar Variáveis CSS

```css
:root {
    --color-primary: #3B82F6;
    --color-secondary: #6B7280;
    --color-success: #10B981;
    --color-warning: #F59E0B;
    --color-danger: #EF4444;
    
    --font-family: 'Inter', sans-serif;
    --border-radius: 0.5rem;
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background-color: var(--color-primary);
}
```

### 3. Minificar em Produção

```php
// config/assets-compress.php
return [
    'jsCompressor' => 'uglifyjs {from} -o {to}',
    'cssCompressor' => 'cleancss {from} -o {to}',
    'bundles' => [
        'frontend\assets\AppAsset',
    ],
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
];
```

---

## Próximos Passos

- [Views](views.md)
- [Layouts](layouts.md)
