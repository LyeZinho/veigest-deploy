# Relatório de Implementação - Sistema de Relatórios VeiGest

**Data:** 21 de Novembro de 2025  
**Versão:** 1.0  
**Autor:** Equipe de Desenvolvimento VeiGest

---

## 1. Resumo Executivo

Este documento descreve a implementação completa do módulo de Relatórios do sistema VeiGest. O módulo fornece análises detalhadas sobre combustível, manutenções e custos operacionais dos veículos da frota, com visualizações gráficas interativas e exportação de dados.

---

## 2. Arquitetura Implementada

### 2.1. Estrutura de Ficheiros

```
veigest/
├── common/models/
│   ├── FuelLog.php          # Modelo para registos de combustível
│   ├── Maintenance.php      # Modelo para manutenções
│   └── Alert.php            # Modelo para alertas
│
├── frontend/
│   ├── controllers/
│   │   └── ReportController.php    # Controlador principal de relatórios
│   │
│   └── views/report/
│       ├── index.php         # Dashboard principal de relatórios
│       ├── fuel.php          # Relatório detalhado de combustível
│       ├── maintenance.php   # Relatório detalhado de manutenções
│       └── vehicles.php      # Análise comparativa de veículos
```

### 2.2. Padrão MVC Seguido

O módulo segue rigorosamente o padrão MVC do Yii2:

- **Models (Modelos):** Contêm a lógica de negócio, validações e métodos estáticos para consultas estatísticas
- **Views (Vistas):** Responsáveis pela apresentação visual com gráficos Chart.js
- **Controllers (Controladores):** Coordenam o fluxo de dados entre modelos e vistas

---

## 3. Modelos Implementados

### 3.1. FuelLog (Registo de Combustível)

**Localização:** `/common/models/FuelLog.php`

**Campos da Tabela `fuel_logs`:**
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| company_id | INT | ID da empresa (multi-tenancy) |
| vehicle_id | INT | Veículo relacionado |
| user_id | INT | Utilizador que registou |
| date | DATE | Data do abastecimento |
| liters | DECIMAL(10,2) | Quantidade de litros |
| value | DECIMAL(10,2) | Valor total em € |
| price_per_liter | DECIMAL(10,3) | Preço por litro |
| current_mileage | INT | Quilometragem atual |
| fuel_type | VARCHAR | Tipo de combustível |
| station | VARCHAR | Nome do posto |
| notes | TEXT | Observações |

**Métodos Estáticos Principais:**

```php
// Obtém estatísticas gerais por empresa
FuelLog::getStatsByCompany($companyId, $startDate = null, $endDate = null)
// Retorna: ['total_liters', 'total_value', 'avg_price_per_liter', 'total_records']

// Obtém consumo mensal agregado
FuelLog::getMonthlyConsumption($companyId, $startDate = null, $endDate = null)
// Retorna: array de ['year', 'month', 'month_label', 'total_liters', 'total_value', 'record_count']

// Obtém consumo por veículo
FuelLog::getConsumptionByVehicle($companyId, $startDate = null, $endDate = null)
// Retorna: array de ['vehicle_id', 'license_plate', 'brand', 'model', 'total_liters', 'total_value', 'refuel_count']
```

### 3.2. Maintenance (Manutenção)

**Localização:** `/common/models/Maintenance.php`

**Campos da Tabela `maintenances`:**
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| company_id | INT | ID da empresa |
| vehicle_id | INT | Veículo relacionado |
| type | VARCHAR | Tipo de manutenção |
| description | TEXT | Descrição do serviço |
| date | DATE | Data da manutenção |
| scheduled_date | DATE | Data agendada (futuro) |
| cost | DECIMAL(10,2) | Custo em € |
| mileage | INT | Quilometragem |
| status | VARCHAR | Status: scheduled, in_progress, completed, cancelled |
| notes | TEXT | Observações |

**Constantes de Tipos:**
```php
const TYPE_PREVENTIVE = 'preventive';     // Manutenção Preventiva
const TYPE_CORRECTIVE = 'corrective';     // Manutenção Corretiva
const TYPE_INSPECTION = 'inspection';     // Inspeção
const TYPE_OIL_CHANGE = 'oil_change';     // Troca de Óleo
const TYPE_TIRE = 'tire';                 // Pneus
const TYPE_BRAKE = 'brake';               // Travões
const TYPE_OTHER = 'other';               // Outros
```

**Métodos Estáticos Principais:**

```php
// Estatísticas gerais
Maintenance::getStatsByCompany($companyId, $startDate, $endDate)
// Retorna: ['total_maintenances', 'total_cost', 'avg_cost', 'max_cost', 'min_cost']

// Custos mensais
Maintenance::getMonthlyCosts($companyId, $startDate, $endDate)

// Custos por tipo de manutenção
Maintenance::getCostsByType($companyId, $startDate, $endDate)

// Custos por veículo
Maintenance::getCostsByVehicle($companyId, $startDate, $endDate)

// Manutenções agendadas futuras
Maintenance::getUpcoming($companyId, $days = 30)
```

### 3.3. Alert (Alerta)

**Localização:** `/common/models/Alert.php`

**Constantes:**
```php
// Tipos de Alerta
const TYPE_MAINTENANCE = 'maintenance';
const TYPE_DOCUMENT = 'document';
const TYPE_FUEL = 'fuel';
const TYPE_OTHER = 'other';

// Prioridades
const PRIORITY_LOW = 'low';
const PRIORITY_MEDIUM = 'medium';
const PRIORITY_HIGH = 'high';
const PRIORITY_CRITICAL = 'critical';

// Status
const STATUS_ACTIVE = 'active';
const STATUS_RESOLVED = 'resolved';
const STATUS_IGNORED = 'ignored';
```

---

## 4. Controlador de Relatórios

**Localização:** `/frontend/controllers/ReportController.php`

### 4.1. Actions Disponíveis

| Action | URL | Descrição |
|--------|-----|-----------|
| `actionIndex()` | `/report/index` | Dashboard principal |
| `actionFuel()` | `/report/fuel` | Relatório de combustível |
| `actionMaintenance()` | `/report/maintenance` | Relatório de manutenções |
| `actionVehicles()` | `/report/vehicles` | Análise de veículos |
| `actionGetChartData()` | `/report/get-chart-data` | API JSON para gráficos |
| `actionExportCsv()` | `/report/export-csv` | Exportação de dados |

### 4.2. Controlo de Acesso

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'], // Apenas utilizadores autenticados
                ],
            ],
        ],
    ];
}
```

### 4.3. Filtros de Período

O sistema suporta os seguintes períodos predefinidos:

```php
public static function getPeriodOptions()
{
    return [
        'week' => 'Última Semana',
        'month' => 'Último Mês',
        '3months' => 'Últimos 3 Meses',
        '6months' => 'Últimos 6 Meses',
        'year' => 'Último Ano',
        'all' => 'Todo o Período',
    ];
}
```

### 4.4. Multi-tenancy

Todas as consultas são automaticamente filtradas pelo `company_id` do utilizador autenticado:

```php
$companyId = Yii::$app->user->identity->company_id;
// Todas as queries usam: WHERE company_id = :companyId
```

---

## 5. Vistas Implementadas

### 5.1. Dashboard Principal (`index.php`)

**Funcionalidades:**
- 4 Cards KPI: Total Combustível, Total Manutenções, Alertas Ativos, Total Veículos
- Gráfico de Barras: Consumo de Combustível Mensal
- Gráfico de Barras: Custos de Manutenção Mensal
- Gráfico Doughnut: Manutenções por Categoria
- Gráfico Pie: Documentos por Status
- Tabela resumo por veículo
- Botão de exportação CSV

### 5.2. Relatório de Combustível (`fuel.php`)

**Funcionalidades:**
- Filtros: Período e Veículo específico
- KPIs: Litros totais, Custo total, Preço médio/litro, Nº abastecimentos
- Gráfico de linha: Evolução mensal (custo vs litros)
- Gráfico doughnut: Consumo por veículo
- Tabela: Consumo detalhado por veículo
- Tabela: Últimos abastecimentos

### 5.3. Relatório de Manutenções (`maintenance.php`)

**Funcionalidades:**
- Filtros: Período e Veículo específico
- KPIs: Custo total, Nº manutenções, Custo médio, Agendadas
- Gráfico de barras: Custos mensais
- Gráfico doughnut: Custos por tipo
- Gráfico horizontal: Custos por veículo
- Tabela: Manutenções agendadas (com countdown)
- Tabelas: Resumo por tipo e por veículo
- Tabela: Últimas manutenções

### 5.4. Análise de Veículos (`vehicles.php`)

**Funcionalidades:**
- Filtro: Período
- KPIs: Veículos analisados, Total combustível, Total manutenções, Custo total frota
- Gráfico horizontal: Custo total por veículo
- Gráfico agrupado: Combustível vs Manutenção por veículo
- Gráfico pie: Distribuição de custos
- Gráfico doughnut: Combustível vs Manutenção (totais)
- Tabela completa com todos os dados e % do total
- Rankings: Top 5 mais caros e Top 5 mais económicos

---

## 6. Tecnologias Utilizadas

### 6.1. Backend
- **Framework:** Yii2 Advanced Template
- **PHP:** >= 7.4
- **Base de Dados:** MySQL 8.0

### 6.2. Frontend
- **CSS Framework:** AdminLTE 3.x / Bootstrap 4
- **Gráficos:** Chart.js 3.x
- **Ícones:** Font Awesome 5

### 6.3. Bibliotecas Chart.js

Os gráficos são carregados via CDN no layout principal. Para adicionar novos gráficos:

```javascript
const ctx = document.getElementById('chartId').getContext('2d');
new Chart(ctx, {
    type: 'bar', // bar, line, pie, doughnut, radar, etc.
    data: {
        labels: [...],
        datasets: [{
            label: 'Label',
            data: [...],
            backgroundColor: 'rgba(...)' 
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        // ... outras opções
    }
});
```

---

## 7. Guia para Implementar Novas Funcionalidades

### 7.1. Adicionar Novo Tipo de Relatório

**Passo 1:** Criar método no modelo (se necessário)

```php
// Em common/models/SeuModelo.php
public static function getCustomStats($companyId, $startDate, $endDate)
{
    return (new Query())
        ->select([
            'COUNT(*) as total',
            'SUM(valor) as total_valor',
        ])
        ->from('sua_tabela')
        ->where(['company_id' => $companyId])
        ->andFilterWhere(['>=', 'date', $startDate])
        ->andFilterWhere(['<=', 'date', $endDate])
        ->one();
}
```

**Passo 2:** Adicionar action no controlador

```php
// Em frontend/controllers/ReportController.php
public function actionNovoRelatorio()
{
    $companyId = Yii::$app->user->identity->company_id;
    $period = Yii::$app->request->get('period', 'month');
    list($startDate, $endDate) = $this->calculateDateRange($period);
    
    $stats = SeuModelo::getCustomStats($companyId, $startDate, $endDate);
    
    return $this->render('novo-relatorio', [
        'stats' => $stats,
        'period' => $period,
    ]);
}
```

**Passo 3:** Criar a vista

```php
// Em frontend/views/report/novo-relatorio.php
<?php
use yii\helpers\Html;
$this->title = 'Novo Relatório';
$this->params['breadcrumbs'][] = ['label' => 'Relatórios', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- Conteúdo do relatório -->
```

### 7.2. Adicionar Novo Gráfico

```php
// Na vista, adicionar o canvas
<canvas id="meuNovoGrafico"></canvas>

// No JavaScript (final do ficheiro)
<?php
$dadosJs = json_encode($dados);
$js = <<<JS
const ctx = document.getElementById('meuNovoGrafico').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {$dadosJs}.map(d => d.label),
        datasets: [{
            data: {$dadosJs}.map(d => d.value),
            backgroundColor: 'rgba(54, 162, 235, 0.8)'
        }]
    }
});
JS;
$this->registerJs($js);
?>
```

### 7.3. Adicionar Novo Filtro

```php
// Na vista, dentro do form de filtros
<div class="col-md-3">
    <label>Novo Filtro</label>
    <?= Html::dropDownList('novo_filtro', $novoFiltroValue, [
        '' => 'Todos',
        'opcao1' => 'Opção 1',
        'opcao2' => 'Opção 2',
    ], ['class' => 'form-control']) ?>
</div>

// No controlador, receber o filtro
$novoFiltro = Yii::$app->request->get('novo_filtro');
```

### 7.4. Adicionar Nova Exportação

```php
// Em actionExportCsv, adicionar novo case
case 'novo_tipo':
    $data = SeuModelo::getDataForExport($companyId, $startDate, $endDate);
    $headers = ['Coluna1', 'Coluna2', 'Coluna3'];
    $filename = 'novo_tipo_export.csv';
    break;
```

---

## 8. Criação das Tabelas na Base de Dados

Se as tabelas ainda não existirem, execute as seguintes migrações:

```sql
-- Tabela de Registos de Combustível
CREATE TABLE IF NOT EXISTS `fuel_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `user_id` INT NULL,
    `date` DATE NOT NULL,
    `liters` DECIMAL(10,2) NOT NULL,
    `value` DECIMAL(10,2) NOT NULL,
    `price_per_liter` DECIMAL(10,3) NULL,
    `current_mileage` INT NULL,
    `fuel_type` VARCHAR(50) NULL,
    `station` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`),
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Manutenções
CREATE TABLE IF NOT EXISTS `maintenances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `user_id` INT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'other',
    `description` TEXT NOT NULL,
    `date` DATE NULL,
    `scheduled_date` DATE NULL,
    `cost` DECIMAL(10,2) NULL DEFAULT 0,
    `mileage` INT NULL,
    `status` VARCHAR(20) DEFAULT 'scheduled',
    `service_provider` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`),
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Alertas
CREATE TABLE IF NOT EXISTS `alerts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `vehicle_id` INT NULL,
    `user_id` INT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'other',
    `priority` VARCHAR(20) DEFAULT 'medium',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NULL,
    `status` VARCHAR(20) DEFAULT 'active',
    `due_date` DATE NULL,
    `resolved_at` TIMESTAMP NULL,
    `resolved_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`),
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`resolved_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índices para melhor performance
CREATE INDEX idx_fuel_logs_company ON fuel_logs(company_id);
CREATE INDEX idx_fuel_logs_vehicle ON fuel_logs(vehicle_id);
CREATE INDEX idx_fuel_logs_date ON fuel_logs(date);

CREATE INDEX idx_maintenances_company ON maintenances(company_id);
CREATE INDEX idx_maintenances_vehicle ON maintenances(vehicle_id);
CREATE INDEX idx_maintenances_date ON maintenances(date);
CREATE INDEX idx_maintenances_status ON maintenances(status);

CREATE INDEX idx_alerts_company ON alerts(company_id);
CREATE INDEX idx_alerts_status ON alerts(status);
CREATE INDEX idx_alerts_priority ON alerts(priority);
```

---

## 9. Teste do Sistema

### 9.1. Acesso aos Relatórios

1. Acesse o sistema em `http://192.168.1.100:8001`
2. Faça login com um utilizador válido
3. No menu lateral, clique em **Relatórios**
4. Navegue entre os diferentes tipos de relatórios

### 9.2. Verificação de Funcionalidades

- [ ] Dashboard carrega corretamente
- [ ] Filtros de período funcionam
- [ ] Gráficos são renderizados
- [ ] Exportação CSV gera arquivo válido
- [ ] Multi-tenancy funciona (dados da empresa correta)

---

## 10. Considerações de Performance

### 10.1. Queries Otimizadas

Todas as consultas usam:
- Índices nas colunas de busca
- Agregações no lado do banco de dados
- Limitação de resultados onde apropriado

### 10.2. Cache (Recomendação Futura)

Para melhorar ainda mais a performance, considere implementar cache:

```php
$cacheKey = "report_fuel_stats_{$companyId}_{$period}";
$stats = Yii::$app->cache->getOrSet($cacheKey, function() use ($companyId, $startDate, $endDate) {
    return FuelLog::getStatsByCompany($companyId, $startDate, $endDate);
}, 3600); // Cache por 1 hora
```

---

## 11. Próximos Passos Sugeridos

1. **Implementar Dashboard Widgets:** Widgets reutilizáveis para outros dashboards
2. **Relatórios em PDF:** Geração de relatórios formatados para impressão
3. **Agendamento de Relatórios:** Envio automático por email
4. **Comparativos Temporais:** Comparação mês a mês, ano a ano
5. **Alertas Automáticos:** Notificações baseadas em thresholds
6. **API REST:** Endpoints para integração externa

---

## 12. Suporte

Para dúvidas ou suporte:
- Documentação Yii2: https://www.yiiframework.com/doc/guide/2.0/
- Chart.js: https://www.chartjs.org/docs/latest/
- AdminLTE: https://adminlte.io/docs/3.0/

---

*Documento gerado automaticamente pelo sistema VeiGest*
