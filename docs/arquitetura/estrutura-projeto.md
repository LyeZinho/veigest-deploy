# 📁 Estrutura do Projeto VeiGest

## Árvore de Diretórios Principal

```
veigest/
├── backend/                    # Aplicação Backend (API REST)
│   ├── config/                 # Configurações do backend
│   ├── controllers/            # Controllers web do backend
│   ├── modules/
│   │   └── api/                # ⭐ MÓDULO DA API REST
│   │       ├── components/     # Componentes (ApiAuthenticator)
│   │       ├── controllers/    # Controllers REST
│   │       ├── models/         # Models específicos da API
│   │       └── docs/           # Documentação da API
│   ├── runtime/                # Logs e cache (gitignore)
│   ├── tests/                  # Testes do backend
│   ├── views/                  # Views do backend admin
│   └── web/                    # Document root (index.php)
│
├── frontend/                   # Aplicação Frontend (Interface Web)
│   ├── assets/                 # Asset Bundles (CSS/JS)
│   ├── config/                 # Configurações do frontend
│   ├── controllers/            # Controllers das páginas
│   ├── models/                 # Models de formulários
│   ├── runtime/                # Logs e cache
│   ├── tests/                  # Testes do frontend
│   ├── views/                  # Templates das páginas
│   │   ├── dashboard/          # Views do dashboard
│   │   ├── document/           # Gestão documental
│   │   ├── layouts/            # Layouts base
│   │   ├── report/             # Relatórios
│   │   └── site/               # Páginas públicas
│   └── web/                    # Document root
│       ├── css/                # CSS compilado
│       └── js/                 # JavaScript
│
├── common/                     # Código Partilhado
│   ├── config/                 # Configurações comuns
│   ├── fixtures/               # Dados de teste
│   ├── mail/                   # Templates de email
│   ├── models/                 # ⭐ MODELS ACTIVERECORD
│   ├── tests/                  # Testes comuns
│   └── widgets/                # Widgets reutilizáveis
│
├── console/                    # Aplicação Console (CLI)
│   ├── config/                 # Configurações CLI
│   ├── controllers/            # Comandos personalizados
│   ├── migrations/             # ⭐ MIGRATIONS DE BD
│   └── runtime/                # Logs CLI
│
├── environments/               # Configurações por ambiente
│   ├── dev/                    # Desenvolvimento
│   └── prod/                   # Produção
│
├── vendor/                     # Dependências Composer
├── docker-compose.yml          # Orquestração Docker
├── composer.json               # Dependências PHP
└── init                        # Script de inicialização
```

## Detalhes por Diretório

### `backend/modules/api/` - API REST

```
api/
├── Module.php                  # Configuração do módulo
├── components/
│   └── ApiAuthenticator.php    # Autenticação Bearer Token
├── controllers/
│   ├── BaseApiController.php   # Controller base (CORS, auth)
│   ├── AuthController.php      # Login, logout, refresh
│   ├── VehicleController.php   # CRUD veículos
│   ├── MaintenanceController.php
│   ├── FuelLogController.php
│   ├── UserController.php
│   ├── CompanyController.php
│   ├── DocumentController.php
│   ├── FileController.php
│   ├── RouteController.php
│   └── TicketController.php
├── models/                     # Models específicos da API
│   ├── Vehicle.php
│   ├── Maintenance.php
│   ├── FuelLog.php
│   └── ...
└── docs/                       # Documentação interna
```

### `frontend/controllers/` - Controllers Web

| Controller | Responsabilidade |
|------------|------------------|
| `SiteController` | Páginas públicas, login, registo |
| `DashboardController` | Dashboard principal, KPIs |
| `ReportController` | Relatórios (veículos, manutenção, combustível) |
| `DocumentController` | Gestão documental, upload |
| `GestorController` | Funcionalidades de gestor |
| `CondutorController` | Funcionalidades de condutor |

### `frontend/views/` - Templates

```
views/
├── layouts/
│   ├── main.php            # Layout páginas públicas
│   ├── dashboard.php       # Layout área logada (sidebar)
│   └── login.php           # Layout página de login
├── dashboard/
│   ├── index.php           # Dashboard principal
│   ├── vehicles.php        # Lista de veículos
│   ├── maintenance.php     # Manutenções
│   ├── drivers.php         # Condutores
│   ├── documents.php       # Documentos
│   └── alerts.php          # Alertas
├── report/
│   ├── index.php           # Relatório geral
│   ├── vehicles.php        # Relatório de veículos
│   ├── maintenance.php     # Relatório de manutenção
│   └── fuel.php            # Relatório de combustível
├── document/
│   ├── index.php           # Lista de documentos
│   ├── create.php          # Upload
│   ├── view.php            # Visualizar
│   └── _form.php           # Formulário parcial
└── site/
    ├── index.php           # Homepage
    ├── login.php           # Login
    ├── signup.php          # Registo
    ├── contact.php         # Contacto
    └── error.php           # Página de erro
```

### `common/models/` - Models Partilhados

| Model | Tabela | Descrição |
|-------|--------|-----------|
| `User` | `user` | Utilizadores e autenticação |
| `Vehicle` | `vehicles` | Veículos da frota |
| `Maintenance` | `maintenances` | Registos de manutenção |
| `FuelLog` | `fuel_logs` | Abastecimentos |
| `Document` | `documents` | Documentos associados |
| `File` | `files` | Ficheiros uploadados |
| `Alert` | `alerts` | Alertas do sistema |
| `Company` | `companies` | Empresas (multi-tenant) |
| `Route` | `routes` | Rotas de transporte |
| `Ticket` | `tickets` | Bilhetes |

### `console/migrations/` - Migrations

Ficheiro principal:
```
m251121_000000_veigest_consolidated_migration.php
```

Contém toda a estrutura da base de dados:
- Tabelas principais
- Tabelas RBAC
- Views
- Índices e FKs

## Ficheiros de Configuração Importantes

| Ficheiro | Localização | Propósito |
|----------|-------------|-----------|
| `main.php` | `*/config/` | Config principal de cada app |
| `main-local.php` | `*/config/` | Config local (não versionado) |
| `params.php` | `*/config/` | Parâmetros da aplicação |
| `db.php` | `common/config/` | Conexão à base de dados |
| `.env` | raiz | Variáveis de ambiente |
| `docker-compose.yml` | raiz | Serviços Docker |

## Convenções de Nomenclatura

### Ficheiros
- Controllers: `NomeController.php`
- Models: `Nome.php` (singular)
- Views: `kebab-case.php`
- Migrations: `mYYMMDD_HHMMSS_descricao.php`

### Classes e Métodos
```php
// Controllers
class VehicleController extends Controller
{
    public function actionIndex() { }      // GET /vehicle
    public function actionView($id) { }    // GET /vehicle/123
    public function actionCreate() { }     // POST /vehicle
    public function actionUpdate($id) { }  // PUT /vehicle/123
    public function actionDelete($id) { }  // DELETE /vehicle/123
}

// Models
class Vehicle extends ActiveRecord
{
    public function rules() { }
    public function attributeLabels() { }
    public function getCompany() { }       // Relação
}
```

### URLs
- Frontend: `?r=controller/action` ou `/controller/action`
- API: `/api/recurso` ou `/api/recurso/123`

## Próximos Passos

- [Fluxo de Requisições](fluxo-requisicoes.md)
- [Controllers da API](../backend/api-controllers.md)
