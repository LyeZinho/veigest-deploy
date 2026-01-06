# VeiGest API REST - Backend

Esta é a implementação oficial da API RESTful para o sistema VeiGest, seguindo rigorosamente os padrões REST e as melhores práticas de desenvolvimento de APIs.

## 📚 Documentação Completa

👉 **[Consulte a Documentação Completa da API](API_DOCUMENTATION.md)**

A documentação inclui:
- ✅ Todos os endpoints detalhados com exemplos
- ✅ Guia completo de autenticação
- ✅ Configuração passo a passo
- ✅ Códigos de status HTTP
- ✅ Exemplos de uso com cURL
- ✅ Troubleshooting e resolução de problemas

## 🚀 Quick Start

### 1. Fazer Login

```bash
curl -X POST http://localhost:8002/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}'
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6ImFkbWluIi4uLg==",
    "token_type": "Bearer",
    "expires_in": 86400
  }
}
```

### 2. Usar Token nas Requisições

```bash
curl -X GET http://localhost:8002/api/vehicle \
  -H "Authorization: Bearer {seu_token}"
```

## 🏗️ Arquitetura da API

### Estrutura de Diretórios

```
backend/modules/api/
├── Module.php                     # Módulo principal da API
├── components/
│   ├── ApiAuthenticator.php      # Autenticação Bearer Token
│   └── MqttPublisher.php         # Integração MQTT para alertas
├── controllers/
│   ├── BaseApiController.php     # Controlador base com CORS e auth
│   ├── AuthController.php        # Autenticação (login, logout, me)
│   ├── CompanyController.php     # Gestão de empresas
│   ├── VehicleController.php     # CRUD de veículos
│   ├── UserController.php        # Gestão de usuários/condutores
│   ├── MaintenanceController.php # Manutenções e relatórios
│   ├── FuelLogController.php     # Abastecimentos e estatísticas
│   ├── RouteController.php       # Gestão de rotas
│   ├── AlertController.php       # Alertas com MQTT
│   ├── DocumentController.php    # Gestão documental
│   └── ActivityLogController.php # Log de atividades
└── models/
    ├── Company.php               # Modelo de empresa
    ├── Vehicle.php               # Modelo de veículo
    ├── Maintenance.php           # Modelo de manutenção
    ├── FuelLog.php               # Modelo de abastecimento
    ├── Route.php                 # Modelo de rota
    ├── Alert.php                 # Modelo de alerta
    ├── Document.php              # Modelo de documento
    └── ActivityLog.php           # Modelo de log
```

## 📡 Módulos Disponíveis

### 🔐 Autenticação
- `POST /auth/login` - Login com username/password
- `GET /auth/me` - Perfil do usuário autenticado
- `POST /auth/refresh` - Renovar token
- `POST /auth/logout` - Logout
- `GET /auth/info` - Informações da API

### 🏢 Empresas
- CRUD completo de empresas
- Listagem de veículos e usuários por empresa
- Estatísticas detalhadas (veículos, manutenções, combustível)

### 🚗 Veículos
- CRUD completo com multi-tenancy
- Listagem de manutenções e abastecimentos por veículo
- Estatísticas de consumo e custos
- Filtros por status, marca, tipo de combustível

### 👥 Usuários
- CRUD de usuários e condutores
- Gestão de perfil e foto
- Listagem por empresa e função
- Controle de acesso RBAC

### 🔧 Manutenções
- CRUD completo
- Agendamento de manutenções
- Relatórios mensais e de custos
- Filtros por veículo, tipo e status

### ⛽ Abastecimentos
- CRUD de registros de abastecimento
- Estatísticas de consumo e eficiência
- Alertas de consumo anormal
- Relatórios de custos e km/litro

### 📍 Rotas
- CRUD de rotas
- Gestão de trajetos por veículo e condutor
- Estatísticas de distância e duração

### 🚨 Alertas
- CRUD de alertas
- Priorização (low, medium, high, critical)
- Integração MQTT para notificações em tempo real
- Geração automática de alertas de manutenção

### 📄 Documentos
- CRUD de documentos com upload
- Gestão de documentos por veículo/condutor
- Alertas de documentos próximos ao vencimento

## ⚙️ Características Principais

- ✅ **RESTful** - Seguindo padrões REST
- ✅ **Multi-tenancy** - Isolamento automático por empresa
- ✅ **RBAC** - Controle de acesso baseado em funções
- ✅ **CORS** - Configurado para cross-origin requests
- ✅ **Autenticação** - Bearer Token (Base64)
- ✅ **Validação** - Validação completa de dados
- ✅ **Paginação** - Suporte em todas as listagens
- ✅ **Filtros** - Filtros avançados via query params
- ✅ **MQTT** - Mensageria em tempo real para alertas
- ✅ **Auditoria** - Log de todas as atividades

## 🧪 Testes

Execute os testes automatizados:

```bash
cd backend/modules/api-tests

# Teste de conectividade básica
node test-connectivity-complete.js

# Suite completa de testes
npm test

# Testes individuais
npm run test:auth
npm run test:vehicles
npm run test:maintenance
```

## 🔧 Configuração

### Registrar Módulo

Em `backend/config/main.php`:

```php
'modules' => [
    'api' => [
        'class' => 'backend\modules\api\Module',
    ],
],
```

### Configurar Rotas

Veja detalhes completos em [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

## 📖 Documentos Relacionados

- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - Documentação completa dos endpoints
- **[docs/MQTT_MESSAGING.md](docs/MQTT_MESSAGING.md)** - Documentação do sistema MQTT

## 📞 Suporte

- **Documentação:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Testes:** `backend/modules/api-tests/`
- **Logs:** `backend/runtime/logs/app.log`

---

**Versão:** 1.0  
**Última Atualização:** 06/01/2026
