# 🧪 Guia: Testes

## Visão Geral

O VeiGest utiliza múltiplas estratégias de teste:

1. **Testes de API** - Node.js com assertions simples
2. **Testes Codeception** - PHPUnit para backend/frontend
3. **Testes Manuais** - cURL e Postman

---

## Testes de API (Node.js)

### Estrutura

```
api-tests/
├── package.json
├── run-all-tests.js      # Runner principal
├── utils/
│   └── http-client.js    # Cliente HTTP reutilizável
└── tests/
    ├── test-auth.js      # Testes de autenticação
    ├── test-users.js     # Testes de utilizadores
    └── test-vehicles.js  # Testes de veículos
```

### HTTP Client

```javascript
// api-tests/utils/http-client.js

class HttpClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.token = null;
    }
    
    setToken(token) {
        this.token = token;
    }
    
    async request(method, path, body = null) {
        const headers = {
            'Content-Type': 'application/json',
        };
        
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        
        const options = {
            method,
            headers,
        };
        
        if (body) {
            options.body = JSON.stringify(body);
        }
        
        try {
            const response = await fetch(`${this.baseUrl}${path}`, options);
            const data = await response.json();
            return data;
        } catch (error) {
            return { success: false, error: error.message };
        }
    }
    
    get(path) { return this.request('GET', path); }
    post(path, body) { return this.request('POST', path, body); }
    put(path, body) { return this.request('PUT', path, body); }
    delete(path) { return this.request('DELETE', path); }
}

module.exports = HttpClient;
```

### Criar um Teste

```javascript
// api-tests/tests/test-vehicles.js

const HttpClient = require('../utils/http-client');
const assert = require('assert');

const client = new HttpClient('http://localhost');

async function runTests() {
    console.log('=== Testes de Veículos ===\n');
    
    // Setup: Login
    const loginRes = await client.post('/api/auth/login', {
        username: 'admin',
        password: 'admin123',
    });
    assert(loginRes.success, 'Login falhou');
    client.setToken(loginRes.data.token);
    
    let createdVehicleId = null;
    
    // Teste 1: Listar veículos
    console.log('1. Listar veículos...');
    const listRes = await client.get('/api/vehicles');
    assert(listRes.success, 'Listar deve ter sucesso');
    assert(Array.isArray(listRes.data), 'Data deve ser array');
    console.log(`✓ ${listRes.data.length} veículos encontrados\n`);
    
    // Teste 2: Criar veículo
    console.log('2. Criar veículo...');
    const createRes = await client.post('/api/vehicles', {
        license_plate: 'XX-00-XX',
        brand: 'Test Brand',
        model: 'Test Model',
        year: 2024,
        fuel_type: 'diesel',
    });
    assert(createRes.success, 'Criar deve ter sucesso');
    assert(createRes.data.id, 'Deve retornar ID');
    createdVehicleId = createRes.data.id;
    console.log(`✓ Veículo criado: ID ${createdVehicleId}\n`);
    
    // Teste 3: Ver veículo
    console.log('3. Ver veículo...');
    const viewRes = await client.get(`/api/vehicles/${createdVehicleId}`);
    assert(viewRes.success, 'Ver deve ter sucesso');
    assert.strictEqual(viewRes.data.license_plate, 'XX-00-XX');
    console.log('✓ Detalhe correcto\n');
    
    // Teste 4: Actualizar veículo
    console.log('4. Actualizar veículo...');
    const updateRes = await client.put(`/api/vehicles/${createdVehicleId}`, {
        color: 'Azul',
        current_mileage: 5000,
    });
    assert(updateRes.success, 'Actualizar deve ter sucesso');
    assert.strictEqual(updateRes.data.color, 'Azul');
    console.log('✓ Actualização correcta\n');
    
    // Teste 5: Filtros
    console.log('5. Testar filtros...');
    const filterRes = await client.get('/api/vehicles?status=active&fuel_type=diesel');
    assert(filterRes.success, 'Filtros devem funcionar');
    console.log(`✓ ${filterRes.data.length} resultados filtrados\n`);
    
    // Teste 6: Eliminar veículo
    console.log('6. Eliminar veículo...');
    const deleteRes = await client.delete(`/api/vehicles/${createdVehicleId}`);
    assert(deleteRes.success, 'Eliminar deve ter sucesso');
    console.log('✓ Veículo eliminado\n');
    
    // Teste 7: Confirmar eliminação
    console.log('7. Verificar eliminação...');
    const verifyRes = await client.get(`/api/vehicles/${createdVehicleId}`);
    assert(!verifyRes.success, 'Veículo não deve existir');
    console.log('✓ Eliminação confirmada\n');
    
    console.log('=== Todos os testes de veículos passaram! ===');
}

runTests().catch(err => {
    console.error('Erro nos testes:', err);
    process.exit(1);
});
```

### Executar Testes

```bash
cd api-tests

# Instalar dependências
npm install

# Executar todos os testes
node run-all-tests.js

# Executar teste específico
node tests/test-vehicles.js
```

---

## Testes Codeception (PHP)

### Estrutura

```
backend/tests/
├── functional/           # Testes funcionais
│   └── ApiCest.php
├── unit/                 # Testes unitários
│   └── models/
│       └── VehicleTest.php
├── _bootstrap.php
├── functional.suite.yml
└── unit.suite.yml
```

### Teste Unitário de Model

```php
<?php
// backend/tests/unit/models/VehicleTest.php

namespace backend\tests\unit\models;

use common\models\Vehicle;
use Codeception\Test\Unit;

class VehicleTest extends Unit
{
    protected $tester;
    
    public function testValidation()
    {
        $vehicle = new Vehicle();
        
        // Campos obrigatórios
        $this->assertFalse($vehicle->validate());
        $this->assertArrayHasKey('company_id', $vehicle->errors);
        $this->assertArrayHasKey('license_plate', $vehicle->errors);
        $this->assertArrayHasKey('brand', $vehicle->errors);
        $this->assertArrayHasKey('model', $vehicle->errors);
    }
    
    public function testValidVehicle()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'AA-00-BB',
            'brand' => 'Volkswagen',
            'model' => 'Golf',
            'year' => 2020,
            'fuel_type' => 'diesel',
        ]);
        
        $this->assertTrue($vehicle->validate());
    }
    
    public function testFuelTypeValidation()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'AA-00-BB',
            'brand' => 'Test',
            'model' => 'Test',
            'fuel_type' => 'invalid_type',
        ]);
        
        $this->assertFalse($vehicle->validate(['fuel_type']));
    }
    
    public function testStatusList()
    {
        $list = Vehicle::getStatusList();
        
        $this->assertIsArray($list);
        $this->assertArrayHasKey('active', $list);
        $this->assertArrayHasKey('maintenance', $list);
        $this->assertArrayHasKey('inactive', $list);
    }
    
    public function testGetFullName()
    {
        $vehicle = new Vehicle([
            'brand' => 'Volkswagen',
            'model' => 'Golf',
        ]);
        
        $this->assertEquals('Volkswagen Golf', $vehicle->getFullName());
    }
}
```

### Teste Funcional de API

```php
<?php
// backend/tests/functional/api/VehicleApiCest.php

namespace backend\tests\functional\api;

use backend\tests\FunctionalTester;

class VehicleApiCest
{
    private $token;
    
    public function _before(FunctionalTester $I)
    {
        // Login para obter token
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);
        $I->seeResponseCodeIs(200);
        $response = json_decode($I->grabResponse(), true);
        $this->token = $response['data']['token'];
    }
    
    public function testListVehicles(FunctionalTester $I)
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        $I->sendGet('/api/vehicles');
        
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]);
    }
    
    public function testCreateVehicle(FunctionalTester $I)
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        $I->haveHttpHeader('Content-Type', 'application/json');
        
        $I->sendPost('/api/vehicles', [
            'license_plate' => 'TEST-001',
            'brand' => 'Test',
            'model' => 'Model',
            'year' => 2024,
        ]);
        
        $I->seeResponseCodeIs(201);
        $I->seeResponseContainsJson(['success' => true]);
    }
    
    public function testUnauthorizedAccess(FunctionalTester $I)
    {
        // Sem token
        $I->sendGet('/api/vehicles');
        $I->seeResponseCodeIs(401);
    }
    
    public function testValidationError(FunctionalTester $I)
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->token);
        $I->haveHttpHeader('Content-Type', 'application/json');
        
        // Sem campos obrigatórios
        $I->sendPost('/api/vehicles', []);
        
        $I->seeResponseCodeIs(422);
        $I->seeResponseContainsJson(['success' => false]);
    }
}
```

### Executar Codeception

```bash
cd veigest

# Executar todos os testes
vendor/bin/codecept run

# Apenas testes unitários
vendor/bin/codecept run unit

# Apenas testes funcionais
vendor/bin/codecept run functional

# Teste específico
vendor/bin/codecept run unit models/VehicleTest

# Com output detalhado
vendor/bin/codecept run --debug
```

---

## Testes com cURL

### Script de Testes

```bash
#!/bin/bash
# test-api.sh

BASE_URL="http://localhost"
TOKEN=""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

pass() { echo -e "${GREEN}✓ $1${NC}"; }
fail() { echo -e "${RED}✗ $1${NC}"; exit 1; }

echo "=== Testes de API VeiGest ==="
echo ""

# 1. Login
echo "1. Testando login..."
RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/login" \
    -H "Content-Type: application/json" \
    -d '{"username":"admin","password":"admin123"}')

if echo "$RESPONSE" | grep -q '"success":true'; then
    TOKEN=$(echo "$RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    pass "Login OK"
else
    fail "Login falhou: $RESPONSE"
fi

# 2. Listar veículos
echo "2. Testando listagem de veículos..."
RESPONSE=$(curl -s -X GET "$BASE_URL/api/vehicles" \
    -H "Authorization: Bearer $TOKEN")

if echo "$RESPONSE" | grep -q '"success":true'; then
    pass "Listagem OK"
else
    fail "Listagem falhou: $RESPONSE"
fi

# 3. Criar veículo
echo "3. Testando criação de veículo..."
RESPONSE=$(curl -s -X POST "$BASE_URL/api/vehicles" \
    -H "Authorization: Bearer $TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
        "license_plate": "TEST-'$(date +%s)'",
        "brand": "Test",
        "model": "Model"
    }')

if echo "$RESPONSE" | grep -q '"success":true'; then
    VEHICLE_ID=$(echo "$RESPONSE" | grep -o '"id":[0-9]*' | cut -d':' -f2)
    pass "Criação OK (ID: $VEHICLE_ID)"
else
    fail "Criação falhou: $RESPONSE"
fi

# 4. Ver veículo
echo "4. Testando visualização..."
RESPONSE=$(curl -s -X GET "$BASE_URL/api/vehicles/$VEHICLE_ID" \
    -H "Authorization: Bearer $TOKEN")

if echo "$RESPONSE" | grep -q '"success":true'; then
    pass "Visualização OK"
else
    fail "Visualização falhou: $RESPONSE"
fi

# 5. Eliminar veículo
echo "5. Testando eliminação..."
RESPONSE=$(curl -s -X DELETE "$BASE_URL/api/vehicles/$VEHICLE_ID" \
    -H "Authorization: Bearer $TOKEN")

if echo "$RESPONSE" | grep -q '"success":true'; then
    pass "Eliminação OK"
else
    fail "Eliminação falhou: $RESPONSE"
fi

echo ""
echo "=== Todos os testes passaram! ==="
```

### Executar

```bash
chmod +x test-api.sh
./test-api.sh
```

---

## Cobertura de Código

### Gerar Relatório

```bash
# Com Codeception + PHPUnit
vendor/bin/codecept run --coverage --coverage-html

# Ver relatório
open tests/_output/coverage/index.html
```

---

## Boas Práticas

### 1. Arrange-Act-Assert

```javascript
// Arrange (preparar)
const vehicle = {
    license_plate: 'XX-00-XX',
    brand: 'Test',
    model: 'Model',
};

// Act (executar)
const response = await client.post('/api/vehicles', vehicle);

// Assert (verificar)
assert(response.success);
assert.strictEqual(response.data.license_plate, 'XX-00-XX');
```

### 2. Testes Independentes

```javascript
// Cada teste deve poder correr isoladamente
// Criar dados de teste no início
// Limpar dados de teste no fim
```

### 3. Dados de Teste Únicos

```javascript
// Usar timestamp para evitar conflitos
const uniquePlate = `TEST-${Date.now()}`;
```

### 4. Testar Casos de Erro

```javascript
// Testar erros de validação
// Testar acesso não autorizado
// Testar recursos não encontrados
```

---

## Próximos Passos

- [Adicionar CRUD](adicionar-crud.md)
- [Erros Comuns](../troubleshooting/erros-comuns.md)
