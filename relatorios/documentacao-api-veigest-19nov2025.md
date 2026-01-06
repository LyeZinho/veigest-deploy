# VeiGest API RESTful - Documentação Técnica

**Projeto:** Sistema de Gestão de Veículos (VeiGest)  
**Curso:** TeSP Em Programação De Sistemas De Informação  
**UC:** Serviços e Interoperabilidade de Sistemas  
**Data:** 19 de Novembro de 2025  
**Versão API:** 1.0.0

---

## 1. Contextualização do Projeto

O VeiGest é um sistema de gestão de frota de veículos desenvolvido para empresas que necessitam de controlo eficiente dos seus veículos, condutores, manutenções e custos operacionais. A API RESTful foi desenvolvida para suportar aplicações cliente Android, fornecendo acesso a todas as funcionalidades do sistema através de endpoints padronizados.

### 1.1 Objetivos da API

- Fornecer operações CRUD completas para entidades principais
- Suportar relações master/detail para dados hierárquicos
- Implementar autenticação segura para aplicações móveis
- Oferecer atualizações em tempo real via messaging
- Manter compatibilidade com padrões REST

### 1.2 Arquitetura Técnica

- **Framework:** Yii2 Advanced Template
- **Base de Dados:** MySQL 9.1.0
- **Servidor Web:** Nginx 1.29.3
- **PHP:** 8.4
- **Versionamento:** API v1 com suporte a versões futuras

---

## 2. Estrutura da API

### 2.1 URL Base
```
http://localhost:8080/api/v1/
```

### 2.2 Formato de Resposta
Todas as respostas são em formato JSON com estrutura consistente:

```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### 2.3 Códigos de Status HTTP
- `200 OK` - Sucesso
- `201 Created` - Recurso criado
- `400 Bad Request` - Dados inválidos
- `401 Unauthorized` - Não autenticado
- `403 Forbidden` - Sem permissão
- `404 Not Found` - Recurso não encontrado
- `500 Internal Server Error` - Erro do servidor

---

## 3. Autenticação

### 3.1 Login
**Endpoint:** `POST /api/v1/auth/login`

**Request Body:**
```json
{
    "username": "admin",
    "password": "admin"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "access_token": "YhvVC4Rr2tX13lhmgFQ63jKNzYX4YNTA",
        "token_type": "Bearer",
        "user": {
            "id": 1,
            "username": "admin",
            "nome": "VeiGest Admin",
            "email": "admin@veigest.com",
            "company_id": 1,
            "company": {
                "id": 1,
                "nome": "VeiGest Demo"
            },
            "estado": "ativo"
        }
    }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost:8080/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'
```

### 3.2 Refresh Token
**Endpoint:** `POST /api/v1/auth/refresh`

**Headers:**
```
Authorization: Bearer YhvVC4Rr2tX13lhmgFQ63jKNzYX4YNTA
```

**cURL Example:**
```bash
curl -X POST "http://localhost:8080/api/v1/auth/refresh" \
  -H "Authorization: Bearer YhvVC4Rr2tX13lhmgFQ63jKNzYX4YNTA"
```

### 3.3 Logout
**Endpoint:** `POST /api/v1/auth/logout`

**Headers:**
```
Authorization: Bearer YhvVC4Rr2tX13lhmgFQ63jKNzYX4YNTA
```

---

## 4. Endpoints CRUD Principais

### 4.1 Companies (Empresas)

#### Listar Empresas
**Endpoint:** `GET /api/v1/company`

**Response:**
```json
[
    {
        "id": 1,
        "nome": "VeiGest Demo",
        "email": "demo@veigest.com",
        "telefone": "+351 123 456 789",
        "nif": "123456789",
        "morada": "Rua Principal, 123",
        "cidade": "Lisboa",
        "codigo_postal": "1000-000",
        "pais": "Portugal",
        "estado": "ativo",
        "created_at": "2025-11-19 15:00:00",
        "updated_at": "2025-11-19 15:00:00"
    }
]
```

**cURL Example:**
```bash
curl -X GET "http://localhost:8080/api/v1/company"
```

#### Obter Empresa Específica
**Endpoint:** `GET /api/v1/company/{id}`

**cURL Example:**
```bash
curl -X GET "http://localhost:8080/api/v1/company/1"
```

#### Criar Empresa
**Endpoint:** `POST /api/v1/company`

**Request Body:**
```json
{
    "nome": "Nova Empresa",
    "email": "nova@empresa.com",
    "telefone": "+351 987 654 321",
    "nif": "987654321",
    "morada": "Rua Nova, 456",
    "cidade": "Porto",
    "codigo_postal": "4000-000",
    "pais": "Portugal"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost:8080/api/v1/company" \
  -H "Content-Type: application/json" \
  -d '{"nome":"Nova Empresa","email":"nova@empresa.com","telefone":"+351 987 654 321"}'
```

#### Atualizar Empresa
**Endpoint:** `PUT /api/v1/company/{id}`

**cURL Example:**
```bash
curl -X PUT "http://localhost:8080/api/v1/company/1" \
  -H "Content-Type: application/json" \
  -d '{"nome":"Empresa Atualizada","telefone":"+351 111 222 333"}'
```

#### Eliminar Empresa
**Endpoint:** `DELETE /api/v1/company/{id}`

**cURL Example:**
```bash
curl -X DELETE "http://localhost:8080/api/v1/company/1"
```

### 4.2 Vehicles (Veículos)

#### Listar Veículos
**Endpoint:** `GET /api/v1/vehicle`

**Response:**
```json
[
    {
        "id": 1,
        "company_id": 1,
        "matricula": "AB-12-CD",
        "marca": "Toyota",
        "modelo": "Corolla",
        "ano": 2020,
        "combustivel": "gasolina",
        "quilometragem": 25000,
        "cor": "branco",
        "numero_chassis": "JTDKB20U583123456",
        "estado": "ativo",
        "created_at": "2025-11-19 15:00:00",
        "updated_at": "2025-11-19 15:00:00"
    }
]
```

#### Criar Veículo
**Request Body:**
```json
{
    "company_id": 1,
    "matricula": "XY-98-ZW",
    "marca": "Volkswagen",
    "modelo": "Golf",
    "ano": 2021,
    "combustivel": "diesel",
    "quilometragem": 15000,
    "cor": "azul",
    "numero_chassis": "WVWZZZ1KZ9W123456"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost:8080/api/v1/vehicle" \
  -H "Content-Type: application/json" \
  -d '{"company_id":1,"matricula":"XY-98-ZW","marca":"Volkswagen","modelo":"Golf","ano":2021}'
```

### 4.3 Users (Utilizadores/Condutores)

#### Listar Utilizadores
**Endpoint:** `GET /api/v1/user`

#### Obter Condutores (utilizadores com carta)
**Endpoint:** `GET /api/v1/user/drivers`

**cURL Example:**
```bash
curl -X GET "http://localhost:8080/api/v1/user/drivers"
```

### 4.4 Maintenances (Manutenções)

#### Listar Manutenções
**Endpoint:** `GET /api/v1/maintenance`

#### Criar Manutenção
**Request Body:**
```json
{
    "vehicle_id": 1,
    "tipo": "preventiva",
    "descricao": "Mudança de óleo e filtros",
    "custo": 150.00,
    "data_manutencao": "2025-12-01",
    "quilometragem": 25000,
    "fornecedor": "AutoRepair Lda",
    "estado": "agendada"
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost:8080/api/v1/maintenance" \
  -H "Content-Type: application/json" \
  -d '{"vehicle_id":1,"tipo":"preventiva","descricao":"Mudança de óleo","custo":150.00}'
```

---

## 5. Relações Master/Detail

### 5.1 Veículos por Empresa
**Endpoint:** `GET /api/v1/company/{id}/vehicles`

**Descrição:** Obter todos os veículos de uma empresa específica

**cURL Example:**
```bash
curl -X GET "http://localhost:8080/api/v1/company/1/vehicles"
```

### 5.2 Utilizadores por Empresa
**Endpoint:** `GET /api/v1/company/{id}/users`

**Descrição:** Obter todos os utilizadores de uma empresa

### 5.3 Manutenções por Veículo
**Endpoint:** `GET /api/v1/vehicle/{id}/maintenances`

**Descrição:** Obter histórico de manutenções de um veículo

**cURL Example:**
```bash
curl -X GET "http://localhost:8080/api/v1/vehicle/1/maintenances"
```

### 5.4 Estatísticas da Empresa
**Endpoint:** `GET /api/v1/company/{id}/stats`

**Response:**
```json
{
    "company": { ... },
    "vehicles_count": 15,
    "users_count": 8,
    "active_vehicles": 12,
    "vehicles_in_maintenance": 3
}
```

### 5.5 Estatísticas do Veículo
**Endpoint:** `GET /api/v1/vehicle/{id}/stats`

**Response:**
```json
{
    "vehicle": { ... },
    "maintenances_count": 5,
    "fuel_logs_count": 20,
    "total_maintenance_cost": 750.00,
    "total_fuel_cost": 1250.00,
    "total_cost": 2000.00,
    "average_fuel_consumption": 6.5
}
```

---

## 6. Funcionalidade de Messaging (Publish/Subscribe)

### 6.1 Server-Sent Events (SSE)
**Endpoint:** `GET /api/v1/messaging/events`

**Descrição:** Stream de eventos em tempo real para atualizações dinâmicas

**cURL Example:**
```bash
curl -X GET "http://localhost:8080/api/v1/messaging/events" \
  -H "Accept: text/event-stream"
```

**Exemplo de Eventos:**
```
data: {"type":"connected","message":"Connected to VeiGest real-time updates","timestamp":"2025-11-19T15:30:00+00:00"}

data: {"type":"vehicle_created","data":{"id":5,"matricula":"ZZ-11-AA","marca":"Ford","modelo":"Focus"},"timestamp":"2025-11-19T15:31:00+00:00"}

data: {"type":"maintenance_created","data":{"id":10,"vehicle_id":5,"tipo":"corretiva","estado":"agendada"},"timestamp":"2025-11-19T15:32:00+00:00"}

data: {"type":"heartbeat","timestamp":"2025-11-19T15:33:00+00:00"}
```

### 6.2 Subscrição por Canais
**Endpoint:** `GET /api/v1/messaging/subscribe?channels=general,vehicles,maintenances`

**Canais Disponíveis:**
- `general` - Mensagens gerais do sistema
- `vehicles` - Atualizações de veículos
- `maintenances` - Notificações de manutenção
- `alerts` - Alertas do sistema

### 6.3 Publicar Mensagem
**Endpoint:** `POST /api/v1/messaging/publish`

**Request Body:**
```json
{
    "channel": "vehicles",
    "message": "New vehicle added",
    "data": {
        "vehicle_id": 5,
        "action": "created"
    }
}
```

**cURL Example:**
```bash
curl -X POST "http://localhost:8080/api/v1/messaging/publish" \
  -H "Content-Type: application/json" \
  -d '{"channel":"vehicles","message":"New vehicle added","data":{"vehicle_id":5}}'
```

### 6.4 Estatísticas de Messaging
**Endpoint:** `GET /api/v1/messaging/stats`

---

## 7. Filtros e Pesquisas

### 7.1 Veículos por Estado
**Endpoint:** `GET /api/v1/vehicle/status/{status}`

**Estados Válidos:**
- `ativo` - Veículos ativos
- `inativo` - Veículos inativos  
- `manutencao` - Veículos em manutenção

**cURL Example:**
```bash
curl -X GET "http://localhost:8080/api/v1/vehicle/status/ativo"
```

### 7.2 Manutenções por Estado
**Endpoint:** `GET /api/v1/maintenance/status/{status}`

**Estados Válidos:**
- `agendada` - Manutenções agendadas
- `em_andamento` - Manutenções em curso
- `concluida` - Manutenções concluídas
- `cancelada` - Manutenções canceladas

### 7.3 Paginação
Todos os endpoints de listagem suportam paginação automática:

**Headers de Resposta:**
```
X-Pagination-Total-Count: 50
X-Pagination-Page-Count: 3
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
```

**Parâmetros de Query:**
- `page` - Número da página (padrão: 1)
- `per-page` - Itens por página (padrão: 20)

**Exemplo:**
```bash
curl -X GET "http://localhost:8080/api/v1/vehicle?page=2&per-page=10"
```

---

## 8. Tratamento de Erros

### 8.1 Estrutura de Erro
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": [
        {
            "field": "matricula",
            "message": "Matricula is required"
        }
    ]
}
```

### 8.2 Códigos de Erro Comuns
- `MISSING_CREDENTIALS` - Credenciais em falta
- `INVALID_CREDENTIALS` - Credenciais inválidas
- `USER_INACTIVE` - Utilizador inativo
- `MISSING_TOKEN` - Token de autenticação em falta
- `INVALID_TOKEN` - Token inválido
- `VALIDATION_ERROR` - Erro de validação de dados

---

## 9. Considerações de Segurança

### 9.1 CORS
A API está configurada para aceitar pedidos cross-origin de qualquer domínio durante o desenvolvimento. Em produção, deve ser restringido aos domínios autorizados.

### 9.2 Autenticação
- Tokens de acesso são gerados e validados para cada sessão
- Tokens podem ser renovados através do endpoint `/refresh`
- Logout invalida o token atual

### 9.3 Validação
- Todos os dados de entrada são validados
- Campos obrigatórios são verificados
- Tipos de dados são validados (email, números, datas)

---

## 10. Exemplos de Integração Android

### 10.1 Configuração Base
```java
public class VeiGestAPI {
    private static final String BASE_URL = "http://localhost:8080/api/v1/";
    private String authToken;
    
    public void setAuthToken(String token) {
        this.authToken = token;
    }
    
    private Map<String, String> getHeaders() {
        Map<String, String> headers = new HashMap<>();
        headers.put("Content-Type", "application/json");
        if (authToken != null) {
            headers.put("Authorization", "Bearer " + authToken);
        }
        return headers;
    }
}
```

### 10.2 Login
```java
public void login(String username, String password, Callback callback) {
    JSONObject requestBody = new JSONObject();
    requestBody.put("username", username);
    requestBody.put("password", password);
    
    // HTTP request implementation
    makeRequest("POST", "auth/login", requestBody, callback);
}
```

### 10.3 Listar Veículos
```java
public void getVehicles(Callback callback) {
    makeRequest("GET", "vehicle", null, callback);
}
```

### 10.4 Server-Sent Events
```java
public void subscribeToUpdates() {
    EventSource.Builder builder = new EventSource.Builder(
        new Request.Builder()
            .url(BASE_URL + "messaging/events")
            .addHeader("Accept", "text/event-stream")
            .build()
    );
    
    EventSource eventSource = builder.build();
}
```

---

## 11. Informações do Servidor de Produção

### 11.1 Endereço do Servidor
**URL:** `http://localhost:8080` (desenvolvimento)  
**Produção:** A definir quando implantado

### 11.2 Credenciais de Acesso
**Utilizador:** admin  
**Password:** admin  

**Base de Dados:**
- Host: localhost
- Porto: 3306
- Schema: veigest
- Utilizador: root

### 11.3 Monitorização
- Logs de erro: `/backend/runtime/logs/app.log`
- Debug: Yii2 Debug Toolbar disponível
- Performance: Métricas nos headers HTTP

---

## 12. Notas de Implementação

### 12.1 Tecnologias Utilizadas
- **Yii2 Framework** - Framework PHP para desenvolvimento rápido
- **ActiveRecord** - ORM para acesso à base de dados
- **RESTful Routing** - Roteamento automático REST
- **Server-Sent Events** - Para atualizações em tempo real
- **JSON** - Formato de dados padrão

### 12.2 Padrões Seguidos
- REST Level 2 (Richardson Maturity Model)
- HTTP Status Codes padronizados
- JSON como formato de resposta
- Versionamento da API (v1)
- CORS para aplicações web

### 12.3 Melhorias Futuras
- Implementação de JWT completo
- Cache de respostas
- Rate limiting
- Documentação Swagger/OpenAPI
- Testes automatizados
- Métricas de performance

---

**Documento gerado automaticamente pelo sistema VeiGest**  
**Data:** 19 de Novembro de 2025  
**Versão:** 1.0.0