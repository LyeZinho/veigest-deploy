# 🏗️ Visão Geral do Sistema VeiGest

## Introdução

O VeiGest é um sistema de gestão de frotas desenvolvido com o framework Yii2 (PHP), seguindo o padrão MVC (Model-View-Controller) e arquitetura RESTful para a API.

## Arquitetura Geral

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTE                                   │
│    (Browser / App Mobile / Sistemas Externos)                    │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                    NGINX / Apache                                │
│              (Reverse Proxy / Load Balancer)                     │
└───────────────────────────┬─────────────────────────────────────┘
                            │
            ┌───────────────┴───────────────┐
            ▼                               ▼
┌───────────────────────┐       ┌───────────────────────┐
│      FRONTEND         │       │      BACKEND          │
│   (Interface Web)     │       │    (API REST)         │
│   Porta: 8001/20080   │       │   Porta: 8002/21080   │
│                       │       │                       │
│  - Dashboard          │       │  - /api/auth          │
│  - Relatórios         │       │  - /api/vehicles      │
│  - Documentos         │       │  - /api/maintenance   │
│  - Gestão             │       │  - /api/fuel-log      │
└───────────┬───────────┘       └───────────┬───────────┘
            │                               │
            └───────────────┬───────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                         COMMON                                   │
│              (Models, Widgets, Configurações)                    │
└───────────────────────────┬─────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────┐
│                        MySQL/MariaDB                             │
│                    (Base de Dados)                               │
│                      Porta: 3306                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Stack Tecnológico

| Componente | Tecnologia | Versão |
|------------|------------|--------|
| **Backend Framework** | Yii2 | 2.0.x |
| **Linguagem** | PHP | 8.2+ |
| **Base de Dados** | MySQL/MariaDB | 8.0+ |
| **Frontend CSS** | TailwindCSS + AdminLTE | 3.x |
| **JavaScript** | Chart.js, Alpine.js | - |
| **Containerização** | Docker + Docker Compose | - |
| **Servidor Web** | Apache (mod_rewrite) | 2.4 |

## Princípios Arquiteturais

### 1. Separação de Responsabilidades
- **Frontend**: Interface de utilizador, renderização de views
- **Backend**: API REST, lógica de negócio
- **Common**: Código partilhado (models, widgets)
- **Console**: Comandos CLI, migrations

### 2. Multi-Tenancy
Cada empresa (`company_id`) tem dados isolados. A filtragem é automática via:
```php
// Em queries
->andWhere(['company_id' => Yii::$app->user->identity->company_id])
```

### 3. Autenticação Stateless (API)
- Bearer Token codificado em Base64
- Token contém: `user_id`, `company_code`, `expires_at`
- Sem sessões no backend API

### 4. RBAC (Role-Based Access Control)
Papéis definidos:
- `admin`: Acesso total
- `gestor`: Gestão da empresa
- `condutor`: Apenas visualização

## Fluxo de Dados Típico

```
1. Utilizador faz login (Frontend)
           │
           ▼
2. Frontend envia credenciais para API (/api/auth/login)
           │
           ▼
3. API valida e retorna token Bearer
           │
           ▼
4. Frontend armazena token e inclui em requisições
           │
           ▼
5. API valida token, executa ação, retorna JSON
           │
           ▼
6. Frontend renderiza dados na view
```

## Componentes Principais

### Frontend (`frontend/`)
- Controllers para páginas web
- Views com templates Blade-like
- Assets (CSS, JS)
- Layouts (dashboard, main, login)

### Backend (`backend/`)
- Módulo API (`backend/modules/api/`)
- Controllers REST
- Models específicos da API
- Autenticação Bearer

### Common (`common/`)
- Models ActiveRecord partilhados
- Widgets reutilizáveis
- Configurações globais
- Fixtures para testes

### Console (`console/`)
- Migrations de base de dados
- Comandos CLI personalizados
- Tarefas agendadas (cron)

## Próximos Passos

- [Estrutura do Projeto](estrutura-projeto.md) - Detalhes de cada pasta
- [Fluxo de Requisições](fluxo-requisicoes.md) - Como as requisições são processadas
