# 📡 Endpoints da API - Referência Completa

## Base URL

```
Desenvolvimento: http://localhost:8002/api
Produção: https://api.veigest.com/api
```

## Headers Obrigatórios

```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## 🔐 Autenticação

### POST /api/auth/login

Login e obtenção de token.

**Request:**
```json
{
    "username": "admin",
    "password": "admin123"
}
```

**Response (200):**
```json
{
    "success": true,
    "message": "Login realizado com sucesso",
    "data": {
        "token": "eyJ1c2VyX2lkIjox...",
        "token_type": "Bearer",
        "expires_at": 1704153600,
        "expires_in": 86400,
        "user": {
            "id": 1,
            "username": "admin",
            "email": "admin@veigest.pt",
            "role": "admin",
            "company_id": 1
        }
    }
}
```

### GET /api/auth/me

Perfil do utilizador autenticado.

**Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "username": "admin",
        "email": "admin@veigest.pt",
        "role": "admin",
        "company": {
            "id": 1,
            "name": "VeiGest Demo",
            "code": "VEI001"
        }
    }
}
```

### POST /api/auth/refresh

Renovar token.

**Response (200):**
```json
{
    "success": true,
    "data": {
        "token": "eyJ1c2VyX2lkIjox...",
        "expires_at": 1704240000
    }
}
```

### POST /api/auth/logout

Invalidar sessão.

**Response (200):**
```json
{
    "success": true,
    "message": "Logout realizado"
}
```

---

## 🚗 Veículos

### GET /api/vehicles

Listar veículos.

**Query Parameters:**
| Param | Tipo | Descrição |
|-------|------|-----------|
| `status` | string | Filtrar por estado (active, maintenance, inactive) |
| `brand` | string | Filtrar por marca |
| `year` | int | Filtrar por ano |
| `page` | int | Página (default: 1) |
| `per_page` | int | Items por página (default: 20) |
| `sort` | string | Campo para ordenar |
| `order` | string | ASC ou DESC |

**Response (200):**
```json
{
    "items": [
        {
            "id": 1,
            "license_plate": "AA-00-BB",
            "brand": "Toyota",
            "model": "Hilux",
            "year": 2022,
            "fuel_type": "diesel",
            "mileage": 45000,
            "status": "active",
            "created_at": "2024-01-01 10:00:00"
        }
    ],
    "_meta": {
        "totalCount": 15,
        "pageCount": 1,
        "currentPage": 1,
        "perPage": 20
    }
}
```

### GET /api/vehicles/{id}

Ver detalhes de um veículo.

**Response (200):**
```json
{
    "id": 1,
    "license_plate": "AA-00-BB",
    "brand": "Toyota",
    "model": "Hilux",
    "year": 2022,
    "fuel_type": "diesel",
    "mileage": 45000,
    "status": "active",
    "driver": {
        "id": 5,
        "username": "joao_condutor",
        "name": "João Silva"
    }
}
```

### POST /api/vehicles

Criar veículo.

**Request:**
```json
{
    "license_plate": "CC-22-DD",
    "brand": "Volkswagen",
    "model": "Transporter",
    "year": 2023,
    "fuel_type": "diesel",
    "mileage": 0,
    "status": "active"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Veículo criado com sucesso",
    "data": {
        "id": 16,
        "license_plate": "CC-22-DD",
        ...
    }
}
```

### PUT /api/vehicles/{id}

Atualizar veículo.

**Request:**
```json
{
    "mileage": 46500,
    "status": "maintenance"
}
```

### DELETE /api/vehicles/{id}

Remover veículo.

**Response (200):**
```json
{
    "success": true,
    "message": "Veículo removido"
}
```

### GET /api/vehicles/{id}/stats

Estatísticas do veículo.

**Response (200):**
```json
{
    "vehicle": { ... },
    "stats": {
        "total_maintenance_cost": 1250.00,
        "total_fuel_cost": 3400.50,
        "total_cost": 4650.50,
        "average_consumption": 8.5,
        "maintenance_count": 5,
        "fuel_log_count": 28
    }
}
```

### GET /api/vehicles/{id}/maintenances

Manutenções do veículo.

### GET /api/vehicles/{id}/fuel-logs

Abastecimentos do veículo.

### GET /api/vehicles/by-status/{status}

Filtrar veículos por estado.

---

## 🔧 Manutenções

### GET /api/maintenance

Listar manutenções.

**Query Parameters:**
| Param | Tipo | Descrição |
|-------|------|-----------|
| `vehicle_id` | int | Filtrar por veículo |
| `type` | string | Filtrar por tipo |
| `date_from` | date | Data inicial |
| `date_to` | date | Data final |

### GET /api/maintenance/{id}

Ver detalhes da manutenção.

### POST /api/maintenance

Criar manutenção.

**Request:**
```json
{
    "vehicle_id": 1,
    "type": "preventive",
    "date": "2024-01-15",
    "cost": 350.00,
    "mileage_record": 45000,
    "workshop": "Oficina Central",
    "notes": "Troca de óleo e filtros"
}
```

### PUT /api/maintenance/{id}

Atualizar manutenção.

### DELETE /api/maintenance/{id}

Remover manutenção.

### GET /api/maintenance/by-vehicle/{vehicle_id}

Manutenções de um veículo.

### GET /api/maintenance/by-status/{estado}

Filtrar por estado.

### POST /api/maintenance/{id}/schedule

Agendar manutenção.

**Request:**
```json
{
    "scheduled_date": "2024-02-15",
    "workshop": "Oficina Norte"
}
```

### GET /api/maintenance/reports/monthly

Relatório mensal.

**Response (200):**
```json
[
    {
        "year": 2024,
        "month": 1,
        "count": 8,
        "total_cost": 2450.00
    },
    {
        "year": 2023,
        "month": 12,
        "count": 5,
        "total_cost": 1800.00
    }
]
```

### GET /api/maintenance/reports/costs

Relatório de custos por tipo.

---

## ⛽ Abastecimentos

### GET /api/fuel-log

Listar abastecimentos.

**Query Parameters:**
| Param | Tipo | Descrição |
|-------|------|-----------|
| `vehicle_id` | int | Filtrar por veículo |
| `date_from` | date | Data inicial |
| `date_to` | date | Data final |

### POST /api/fuel-log

Registar abastecimento.

**Request:**
```json
{
    "vehicle_id": 1,
    "date": "2024-01-10",
    "liters": 55.5,
    "price_per_liter": 1.65,
    "mileage": 45500,
    "station": "Galp Lumiar",
    "fuel_type": "diesel"
}
```

### GET /api/fuel-log/by-vehicle/{vehicle_id}

Abastecimentos de um veículo.

### GET /api/fuel-log/stats

Estatísticas de combustível.

**Response (200):**
```json
{
    "total_liters": 1250.5,
    "total_cost": 2063.33,
    "average_price_per_liter": 1.65,
    "count": 28
}
```

### GET /api/fuel-log/efficiency-report

Relatório de eficiência.

**Response (200):**
```json
[
    {
        "vehicle_id": 1,
        "license_plate": "AA-00-BB",
        "total_liters": 450.0,
        "total_cost": 742.50,
        "total_km": 3825,
        "km_per_liter": 8.5,
        "cost_per_km": 0.19
    }
]
```

### GET /api/fuel-log/alerts

Alertas de consumo anormal.

---

## 👥 Utilizadores

### GET /api/users

Listar utilizadores.

### GET /api/users/{id}

Ver utilizador.

### POST /api/users

Criar utilizador.

**Request:**
```json
{
    "username": "novo_user",
    "email": "novo@email.pt",
    "password": "senhaSegura123",
    "role": "condutor"
}
```

### PUT /api/users/{id}

Atualizar utilizador.

### GET /api/users/drivers

Listar apenas condutores.

### GET /api/users/profile

Perfil do utilizador atual.

---

## 🏢 Empresas

### GET /api/company

Listar empresas (admin).

### GET /api/company/{id}

Ver empresa.

### GET /api/companies/{id}/vehicles

Veículos da empresa.

### GET /api/companies/{id}/users

Utilizadores da empresa.

### GET /api/companies/{id}/stats

Estatísticas da empresa.

**Response (200):**
```json
{
    "company": { ... },
    "stats": {
        "total_vehicles": 15,
        "active_vehicles": 12,
        "total_users": 8,
        "total_drivers": 5,
        "total_maintenance_cost": 15000.00,
        "total_fuel_cost": 25000.00,
        "alerts_count": 3
    }
}
```

---

## 📄 Documentos

### GET /api/documents

Listar documentos.

**Query Parameters:**
| Param | Tipo | Descrição |
|-------|------|-----------|
| `vehicle_id` | int | Filtrar por veículo |
| `driver_id` | int | Filtrar por condutor |
| `type` | string | Tipo de documento |
| `status` | string | valid ou expired |

### POST /api/documents

Criar/associar documento.

### GET /api/documents/by-vehicle/{vehicle_id}

Documentos de um veículo.

### GET /api/documents/by-driver/{driver_id}

Documentos de um condutor.

---

## 📁 Ficheiros

### GET /api/files

Listar ficheiros.

### POST /api/files

Upload de ficheiro.

**Request (multipart/form-data):**
```
file: [binary]
```

### DELETE /api/files/{id}

Remover ficheiro.

### GET /api/files/stats

Estatísticas de armazenamento.

---

## 🧭 Rotas

### GET /api/routes

Listar rotas.

### POST /api/routes

Criar rota.

**Request:**
```json
{
    "vehicle_id": 1,
    "driver_id": 5,
    "start_location": "Lisboa",
    "end_location": "Porto",
    "start_time": "2024-01-15 08:00:00"
}
```

### POST /api/routes/{id}/complete

Marcar rota como completa.

### GET /api/routes/by-vehicle/{vehicle_id}

Rotas de um veículo.

---

## 🎫 Tickets

### GET /api/tickets

Listar tickets.

### POST /api/tickets

Criar ticket.

**Request:**
```json
{
    "route_id": 1,
    "passenger_name": "Maria Silva",
    "passenger_phone": "912345678"
}
```

### POST /api/tickets/{id}/cancel

Cancelar ticket.

### POST /api/tickets/{id}/complete

Marcar ticket como completo.

---

## Códigos de Resposta

| Código | Significado |
|--------|-------------|
| 200 | OK - Sucesso |
| 201 | Created - Recurso criado |
| 204 | No Content - Sem conteúdo (delete) |
| 400 | Bad Request - Dados inválidos |
| 401 | Unauthorized - Token inválido/expirado |
| 403 | Forbidden - Sem permissão |
| 404 | Not Found - Recurso não encontrado |
| 422 | Unprocessable Entity - Validação falhou |
| 500 | Internal Server Error - Erro do servidor |

## Formato de Erro

```json
{
    "success": false,
    "message": "Erro ao criar veículo",
    "errors": {
        "license_plate": ["A matrícula já existe"]
    },
    "code": 400
}
```
