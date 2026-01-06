# 📚 VeiGest API - Documentação Completa

**Versão:** 1.0  
**Base URL:** `http://localhost:8002/api`  
**Formato:** JSON  
**Autenticação:** Bearer Token

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Autenticação](#autenticação)
3. [Configuração](#configuração)
4. [Endpoints](#endpoints)
   - [Autenticação](#endpoints-de-autenticação)
   - [Empresas](#empresas-companies)
   - [Veículos](#veículos-vehicles)
   - [Usuários](#usuários-users)
   - [Manutenções](#manutenções-maintenance)
   - [Abastecimentos](#abastecimentos-fuel-logs)
   - [Rotas](#rotas-routes)
   - [Alertas](#alertas-alerts)
   - [Documentos](#documentos-documents)
5. [Códigos de Status](#códigos-de-status)
6. [Exemplos de Uso](#exemplos-de-uso)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

A API VeiGest é uma API RESTful completa para gestão de frotas, incluindo:

- **🔐 Autenticação** - Login, tokens JWT, segurança
- **🏢 Empresas** - Gestão multi-tenant de empresas
- **🚗 Veículos** - CRUD completo e relatórios
- **👥 Usuários** - Gestão de usuários e condutores
- **🔧 Manutenções** - Registros e agendamento
- **⛽ Abastecimentos** - Controle de combustível
- **📍 Rotas** - Gestão de trajetos
- **🚨 Alertas** - Sistema de notificações (com MQTT)
- **📄 Documentos** - Gestão documental

### Características Principais

- ✅ **Multi-tenancy**: Isolamento automático por empresa
- ✅ **RBAC**: Controle de acesso por função
- ✅ **REST Completo**: Verbos HTTP padrão (GET, POST, PUT, DELETE)
- ✅ **Paginação**: Suporte a paginação em listagens
- ✅ **Filtros**: Filtros avançados por query params
- ✅ **MQTT**: Mensageria em tempo real para alertas
- ✅ **Auditoria**: Log de atividades (ActivityLog)

---

## 🔐 Autenticação

### Como Funciona

1. **Login**: POST para `/api/auth/login` com credenciais
2. **Token**: Recebe um `access_token` (Base64)
3. **Uso**: Incluir em todas as requisições: `Authorization: Bearer {token}`
4. **Expiração**: Token válido por 24 horas

### Estrutura do Token

O token contém (codificado em Base64):
```json
{
  "user_id": 1,
  "username": "admin",
  "company_id": 1,
  "company_code": "VEI001",
  "roles": ["admin"],
  "expires_at": 1704672000
}
```

### Headers Obrigatórios

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## ⚙️ Configuração

### 1. Registrar Módulo API

Adicione em `backend/config/main.php`:

```php
'modules' => [
    'api' => [
        'class' => 'backend\modules\api\Module',
    ],
],
```

### 2. Configurar URL Manager

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        // Autenticação
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/auth',
            'pluralize' => false,
            'extraPatterns' => [
                'POST login' => 'login',
                'POST logout' => 'logout',
                'GET me' => 'me',
                'POST refresh' => 'refresh',
                'GET info' => 'info',
            ],
        ],
        // Veículos
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'api/vehicle',
            'pluralize' => true,
            'extraPatterns' => [
                'GET {id}/maintenances' => 'maintenances',
                'GET {id}/fuel-logs' => 'fuel-logs',
                'GET {id}/stats' => 'stats',
                'GET by-status/{status}' => 'by-status',
            ],
        ],
        // Outros controllers seguem padrão similar
    ],
],
```

### 3. Configurar Base de Dados

Certifique-se de executar as migrations:

```bash
php yii migrate
```

---

## 📡 Endpoints

### Endpoints de Autenticação

#### POST /auth/login
Autentica usuário e retorna token de acesso.

**Request:**
```json
{
  "username": "admin",
  "password": "admin"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIi...",
    "token_type": "Bearer",
    "expires_in": 86400,
    "expires_at": "2026-01-07T12:00:00Z",
    "user": {
      "id": 1,
      "username": "admin",
      "email": "admin@veigest.com",
      "roles": ["admin"],
      "company_id": 1
    }
  },
  "message": "Login realizado com sucesso"
}
```

#### GET /auth/me
Retorna informações do usuário autenticado.

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@veigest.com",
    "full_name": "Administrador",
    "roles": ["admin"],
    "company_id": 1,
    "company": {
      "id": 1,
      "code": "VEI001",
      "name": "VeiGest Transportes"
    }
  }
}
```

#### POST /auth/logout
Invalida o token atual.

#### POST /auth/refresh
Renova o token de acesso.

#### GET /auth/info
Retorna informações sobre a API.

---

### Empresas (Companies)

#### GET /company
Lista todas as empresas (apenas admin).

**Query Params:**
- `page` - Número da página (padrão: 1)
- `per-page` - Itens por página (padrão: 20)
- `search` - Busca por nome/código
- `status` - Filtrar por status (active/inactive)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "VEI001",
      "name": "VeiGest Transportes",
      "email": "contato@veigest.com",
      "phone": "+351 912345678",
      "tax_id": "123456789",
      "address": "Rua Principal, 123",
      "city": "Leiria",
      "postal_code": "2400-000",
      "country": "Portugal",
      "status": "active",
      "created_at": "2025-01-01T10:00:00Z",
      "updated_at": "2025-01-06T15:30:00Z"
    }
  ],
  "pagination": {
    "total": 50,
    "page": 1,
    "per_page": 20,
    "total_pages": 3
  }
}
```

#### GET /company/{id}
Visualiza detalhes de uma empresa.

#### POST /company
Cria nova empresa (apenas admin).

**Request:**
```json
{
  "code": "VEI002",
  "name": "Nova Transportadora",
  "email": "contato@nova.com",
  "phone": "+351 912345678",
  "tax_id": "987654321",
  "address": "Avenida Central, 456",
  "city": "Porto",
  "postal_code": "4000-000",
  "country": "Portugal",
  "status": "active"
}
```

#### PUT /company/{id}
Atualiza dados de uma empresa.

#### GET /companies/{id}/vehicles
Lista veículos da empresa.

#### GET /companies/{id}/users
Lista usuários da empresa.

#### GET /companies/{id}/stats
Retorna estatísticas da empresa.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "vehicles_count": 45,
    "active_vehicles": 42,
    "users_count": 78,
    "drivers_count": 45,
    "maintenance_stats": {
      "total_maintenances": 230,
      "pending_maintenances": 12
    },
    "fuel_stats": {
      "total_fuel_logs": 1540,
      "total_fuel_cost": 45678.90
    }
  }
}
```

---

### Veículos (Vehicles)

#### GET /vehicle
Lista veículos da empresa do usuário.

**Query Params:**
- `page` - Página (padrão: 1)
- `per-page` - Itens por página (padrão: 20)
- `status` - Filtrar por status (active, inactive, maintenance)
- `brand` - Filtrar por marca
- `fuel_type` - Filtrar por tipo de combustível

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "company_id": 1,
      "license_plate": "AA-12-BB",
      "brand": "Mercedes-Benz",
      "model": "Sprinter 316",
      "year": 2022,
      "fuel_type": "diesel",
      "mileage": 45000,
      "status": "active",
      "driver_id": 5,
      "photo": "/uploads/vehicles/vehicle_1.jpg",
      "created_at": "2022-03-15T09:00:00Z",
      "updated_at": "2026-01-05T14:20:00Z"
    }
  ],
  "pagination": {
    "total": 45,
    "page": 1,
    "per_page": 20,
    "total_pages": 3
  }
}
```

#### GET /vehicle/{id}
Detalhes de um veículo específico.

#### POST /vehicle
Cria novo veículo.

**Request:**
```json
{
  "license_plate": "CC-34-DD",
  "brand": "Volkswagen",
  "model": "Crafter",
  "year": 2023,
  "fuel_type": "diesel",
  "mileage": 15000,
  "status": "active",
  "driver_id": 8
}
```

#### PUT /vehicle/{id}
Atualiza dados do veículo.

#### DELETE /vehicle/{id}
Remove veículo (soft delete).

#### GET /vehicle/{id}/maintenances
Lista manutenções do veículo.

#### GET /vehicle/{id}/fuel-logs
Lista abastecimentos do veículo.

#### GET /vehicle/{id}/stats
Estatísticas do veículo.

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_maintenances": 12,
    "total_maintenance_cost": 3450.00,
    "total_fuel_logs": 45,
    "total_fuel_cost": 2340.50,
    "total_fuel_liters": 1890.5,
    "average_consumption": 12.5,
    "cost_per_km": 0.15
  }
}
```

#### GET /vehicle/by-status/{status}
Lista veículos por status.

---

### Usuários (Users)

#### GET /user
Lista usuários da empresa.

**Query Params:**
- `page`, `per-page` - Paginação
- `status` - Filtrar por status (active, inactive)
- `roles` - Filtrar por função (admin, manager, driver)
- `search` - Buscar por nome/email/username

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "company_id": 1,
      "username": "joao.silva",
      "email": "joao.silva@veigest.com",
      "full_name": "João Silva",
      "phone": "+351 912345678",
      "roles": ["driver"],
      "status": "active",
      "profile_photo": "/uploads/users/user_5.jpg",
      "created_at": "2024-06-10T10:00:00Z",
      "last_login": "2026-01-06T08:30:00Z"
    }
  ],
  "pagination": {
    "total": 78,
    "page": 1,
    "per_page": 20,
    "total_pages": 4
  }
}
```

#### POST /user
Cria novo usuário.

**Request:**
```json
{
  "username": "maria.santos",
  "email": "maria.santos@veigest.com",
  "password": "senha123",
  "full_name": "Maria Santos",
  "phone": "+351 923456789",
  "roles": ["driver"],
  "status": "active"
}
```

#### PUT /user/{id}
Atualiza dados do usuário.

#### GET /user/drivers
Lista apenas condutores.

#### GET /user/profile
Perfil completo do usuário autenticado (com estatísticas).

#### GET /user/by-company/{company_id}
Lista usuários de uma empresa específica (admin only).

#### POST /user/{id}/update-photo
Upload de foto de perfil.

---

### Manutenções (Maintenance)

#### GET /maintenance
Lista manutenções da empresa.

**Query Params:**
- `vehicle_id` - Filtrar por veículo
- `type` - Tipo (preventive, corrective, revision, inspection)
- `status` - Status (scheduled, completed, cancelled)
- `start_date`, `end_date` - Filtrar por período
- `workshop` - Filtrar por oficina

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "company_id": 1,
      "vehicle_id": 12,
      "type": "preventive",
      "type_label": "Preventiva",
      "description": "Troca de óleo e filtros",
      "cost": 150.00,
      "date": "2026-01-10",
      "mileage_record": 45000,
      "next_date": "2026-07-10",
      "workshop": "Oficina Central",
      "status": "scheduled",
      "status_label": "Agendada",
      "created_at": "2025-12-20T10:00:00Z",
      "updated_at": "2025-12-20T10:00:00Z"
    }
  ]
}
```

#### POST /maintenance
Cria nova manutenção.

**Request:**
```json
{
  "vehicle_id": 12,
  "type": "preventive",
  "description": "Revisão dos 50.000 km",
  "cost": 250.00,
  "date": "2026-01-15",
  "mileage_record": 50000,
  "next_date": "2026-07-15",
  "workshop": "Oficina Premium",
  "status": "scheduled"
}
```

#### PUT /maintenance/{id}
Atualiza manutenção.

#### DELETE /maintenance/{id}
Remove manutenção.

#### GET /maintenance/by-vehicle/{vehicle_id}
Manutenções de um veículo específico.

#### GET /maintenance/by-status/{status}
Filtra por status (scheduled, completed, cancelled).

#### POST /maintenance/{id}/schedule
Agenda ou reagenda uma manutenção.

#### GET /maintenance/reports/monthly
Relatório mensal de manutenções.

**Query Params:**
- `year` - Ano (padrão: atual)
- `month` - Mês (padrão: atual)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "period": {
      "year": 2026,
      "month": 1,
      "start_date": "2026-01-01",
      "end_date": "2026-01-31"
    },
    "summary": {
      "total_maintenances": 23,
      "total_cost": 3450.00,
      "by_type": {
        "preventive": 15,
        "corrective": 6,
        "revision": 2
      },
      "by_status": {
        "scheduled": 8,
        "completed": 15
      }
    },
    "maintenances": [...]
  }
}
```

#### GET /maintenance/reports/costs
Relatório de custos por período.

**Query Params:**
- `start_date`, `end_date` - Período

#### GET /maintenance/stats
Estatísticas gerais de manutenções.

---

### Abastecimentos (Fuel Logs)

#### GET /fuel-log
Lista abastecimentos da empresa.

**Query Params:**
- `vehicle_id` - Filtrar por veículo
- `start_date`, `end_date` - Período
- `search` - Buscar em notas

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 234,
      "vehicle_id": 12,
      "date": "2026-01-05",
      "liters": 75.5,
      "value": 120.50,
      "price_per_liter": 1.596,
      "current_mileage": 45230,
      "notes": "Posto Shell - A1",
      "consumption_since_last": 450,
      "cost_per_km": 0.268,
      "created_at": "2026-01-05T14:20:00Z"
    }
  ]
}
```

#### POST /fuel-log
Registra novo abastecimento.

**Request:**
```json
{
  "vehicle_id": 12,
  "date": "2026-01-06",
  "liters": 80.0,
  "value": 128.00,
  "current_mileage": 45680,
  "notes": "Posto BP - A8"
}
```

#### PUT /fuel-log/{id}
Atualiza registro de abastecimento.

#### DELETE /fuel-log/{id}
Remove registro.

#### GET /fuel-log/by-vehicle/{vehicle_id}
Abastecimentos de um veículo.

#### GET /fuel-log/stats
Estatísticas de consumo.

**Query Params:**
- `vehicle_id` - Filtrar por veículo
- `period` - Período (monthly, weekly, yearly)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "period": "monthly",
    "vehicle_id": null,
    "summary": {
      "total_fuel_logs": 145,
      "total_liters": 5430.5,
      "total_cost": 8689.60,
      "average_price_per_liter": 1.600,
      "fuel_efficiency": 12.3,
      "cost_per_km": 0.145
    },
    "by_vehicle": {...},
    "monthly_trend": [...]
  }
}
```

#### GET /fuel-log/alerts
Alertas de consumo anormal.

#### GET /fuel-log/efficiency-report
Relatório de eficiência de combustível.

---

### Rotas (Routes)

#### GET /route
Lista rotas da empresa.

**Query Params:**
- `vehicle_id` - Filtrar por veículo
- `driver_id` - Filtrar por condutor
- `status` - Status (scheduled, in_progress, completed)
- `start_date`, `end_date` - Período

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 89,
      "company_id": 1,
      "vehicle_id": 12,
      "driver_id": 5,
      "start_location": "Leiria",
      "end_location": "Lisboa",
      "start_time": "2026-01-06T08:00:00Z",
      "end_time": "2026-01-06T10:30:00Z",
      "distance_km": 145,
      "duration_minutes": 150,
      "status": "completed",
      "notes": "Entrega de mercadorias",
      "created_at": "2026-01-05T16:00:00Z"
    }
  ]
}
```

#### POST /route
Cria nova rota.

**Request:**
```json
{
  "vehicle_id": 12,
  "driver_id": 5,
  "start_location": "Porto",
  "end_location": "Coimbra",
  "start_time": "2026-01-07T09:00:00Z",
  "notes": "Rota de distribuição"
}
```

#### PUT /route/{id}
Atualiza rota.

#### DELETE /route/{id}
Remove rota.

#### POST /route/{id}/complete
Marca rota como concluída.

#### GET /route/by-vehicle/{vehicle_id}
Rotas de um veículo.

#### GET /route/by-driver/{driver_id}
Rotas de um condutor.

#### GET /route/stats
Estatísticas de rotas.

---

### Alertas (Alerts)

#### GET /alert
Lista alertas da empresa.

**Query Params:**
- `type` - Tipo (maintenance, document, fuel, other)
- `priority` - Prioridade (low, medium, high, critical)
- `status` - Status (active, resolved, ignored)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "company_id": 1,
      "type": "maintenance",
      "title": "Manutenção Agendada",
      "description": "Veículo AA-12-BB tem manutenção agendada para amanhã",
      "priority": "high",
      "status": "active",
      "details": {
        "vehicle_id": 12,
        "maintenance_id": 89
      },
      "created_at": "2026-01-05T15:00:00Z",
      "resolved_at": null
    }
  ]
}
```

#### POST /alert
Cria novo alerta.

**Request:**
```json
{
  "type": "fuel",
  "title": "Consumo Elevado",
  "description": "Veículo CC-34-DD apresenta consumo 20% acima da média",
  "priority": "medium",
  "details": {
    "vehicle_id": 15,
    "consumption": 15.8
  }
}
```

#### PUT /alert/{id}
Atualiza alerta.

#### DELETE /alert/{id}
Remove alerta.

#### POST /alert/{id}/resolve
Marca alerta como resolvido.

#### GET /alert/by-type/{type}
Filtra por tipo.

#### GET /alert/by-priority/{priority}
Filtra por prioridade.

#### GET /alert/generate-maintenance
Gera alertas automáticos de manutenção.

**MQTT Integration:**
- Alertas são publicados automaticamente via MQTT
- Tópico: `veigest/company/{company_id}/alerts`
- Permite atualização em tempo real nos clientes

---

### Documentos (Documents)

#### GET /document
Lista documentos da empresa.

**Query Params:**
- `vehicle_id` - Filtrar por veículo
- `driver_id` - Filtrar por condutor
- `type` - Tipo (registration, insurance, inspection, license, other)
- `status` - Status (valid, expired)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 23,
      "company_id": 1,
      "file_id": 156,
      "vehicle_id": 12,
      "driver_id": null,
      "type": "insurance",
      "expiry_date": "2026-12-31",
      "status": "valid",
      "notes": "Seguro contra terceiros",
      "created_at": "2025-01-01T10:00:00Z",
      "file": {
        "id": 156,
        "original_name": "seguro_2026.pdf",
        "size": 245678,
        "path": "/uploads/documents/2025/01/seguro_2026.pdf"
      }
    }
  ]
}
```

#### POST /document
Cria novo documento (com upload).

**Request (multipart/form-data):**
```
file: [arquivo PDF/imagem]
vehicle_id: 12
type: insurance
expiry_date: 2026-12-31
notes: Seguro contra terceiros
```

#### PUT /document/{id}
Atualiza metadados do documento.

#### DELETE /document/{id}
Remove documento.

#### GET /document/by-vehicle/{vehicle_id}
Documentos de um veículo.

#### GET /document/expiring
Documentos próximos do vencimento.

**Query Params:**
- `days` - Dias até vencimento (padrão: 30)

---

## 📊 Códigos de Status

| Código | Descrição | Uso |
|--------|-----------|-----|
| **200** | OK | Operação bem-sucedida |
| **201** | Created | Recurso criado |
| **204** | No Content | Deleção bem-sucedida |
| **400** | Bad Request | Dados inválidos |
| **401** | Unauthorized | Não autenticado |
| **403** | Forbidden | Sem permissão |
| **404** | Not Found | Recurso não encontrado |
| **422** | Unprocessable Entity | Erro de validação |
| **500** | Internal Server Error | Erro do servidor |

---

## 💡 Exemplos de Uso

### Exemplo Completo: Criar Veículo

```bash
# 1. Login
curl -X POST http://localhost:8002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'

# Resposta: {"data":{"access_token":"eyJ1c2VyX..."}}

# 2. Criar veículo
curl -X POST http://localhost:8002/api/vehicle \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer eyJ1c2VyX..." \
  -d '{
    "license_plate": "AA-12-BB",
    "brand": "Mercedes-Benz",
    "model": "Sprinter 316",
    "year": 2022,
    "fuel_type": "diesel",
    "mileage": 45000,
    "status": "active"
  }'
```

### Exemplo: Listar com Filtros

```bash
# Listar veículos ativos da marca Mercedes
curl -X GET "http://localhost:8002/api/vehicle?status=active&brand=Mercedes-Benz&per-page=10" \
  -H "Authorization: Bearer {token}"
```

### Exemplo: Relatório de Manutenções

```bash
# Relatório mensal de janeiro 2026
curl -X GET "http://localhost:8002/api/maintenance/reports/monthly?year=2026&month=1" \
  -H "Authorization: Bearer {token}"
```

---

## 🔧 Troubleshooting

### Erro 401 - Unauthorized

**Causa:** Token ausente, inválido ou expirado.

**Solução:**
1. Verificar se o header `Authorization` está presente
2. Verificar formato: `Bearer {token}`
3. Fazer novo login se token expirou

### Erro 403 - Forbidden

**Causa:** Usuário sem permissão para a operação.

**Solução:**
1. Verificar roles do usuário
2. Operações de admin requerem role `admin`
3. Multi-tenancy: não pode acessar dados de outra empresa

### Erro 422 - Validation Error

**Causa:** Dados enviados não passam nas validações.

**Solução:**
Verificar campo `errors` na resposta:
```json
{
  "success": false,
  "message": "Erro de validação",
  "errors": {
    "license_plate": ["Campo obrigatório"],
    "year": ["Deve ser um número entre 1900 e 2026"]
  }
}
```

### Erro 500 - Internal Server Error

**Causa:** Erro interno do servidor.

**Solução:**
1. Verificar logs do servidor: `backend/runtime/logs/app.log`
2. Verificar configuração do banco de dados
3. Verificar permissões de arquivo

### API Retorna HTML ao Invés de JSON

**Causa:** URL incorreta ou erro no servidor.

**Solução:**
1. Verificar se a URL está correta
2. Verificar se o módulo API está registrado
3. Verificar configuração do `urlManager`
4. Verificar se Pretty URLs estão ativas

### Teste de Conectividade

Execute o teste básico:
```bash
cd backend/modules/api-tests
node test-connectivity-complete.js
```

---

## 📞 Suporte

Para mais informações:
- **Documentação Técnica:** `/veigest/docs/`
- **Testes:** `/veigest/backend/modules/api-tests/`
- **Issues:** GitHub Issues

---

**Última Atualização:** 06/01/2026  
**Versão da API:** 1.0
