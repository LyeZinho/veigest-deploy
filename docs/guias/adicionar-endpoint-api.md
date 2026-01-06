# 🔌 Guia: Adicionar Endpoint API

## Visão Geral

Este guia mostra como adicionar novos endpoints à API REST do VeiGest.

**Exemplo**: Vamos criar endpoints para o CRUD de **Fornecedores** (`Supplier`).

---

## Passo 1: Criar o Controller API

### Ficheiro: `backend/modules/api/controllers/SupplierController.php`

```php
<?php
namespace backend\modules\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use common\models\Supplier;

/**
 * API Controller para Fornecedores
 * 
 * Endpoints:
 * - GET    /api/suppliers          - Listar fornecedores
 * - GET    /api/suppliers/{id}     - Ver fornecedor
 * - POST   /api/suppliers          - Criar fornecedor
 * - PUT    /api/suppliers/{id}     - Actualizar fornecedor
 * - DELETE /api/suppliers/{id}     - Eliminar fornecedor
 * - GET    /api/suppliers/stats    - Estatísticas
 */
class SupplierController extends BaseApiController
{
    /**
     * GET /api/suppliers
     * 
     * @param string|null $category Filtrar por categoria
     * @param string|null $status Filtrar por status
     * @param string|null $search Pesquisa por nome/NIF
     * @param int $page Página (default: 1)
     * @param int $per_page Items por página (default: 20)
     */
    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $query = Supplier::find()->where(['company_id' => $companyId]);
        
        // Filtros
        $category = Yii::$app->request->get('category');
        if ($category) {
            $query->andWhere(['category' => $category]);
        }
        
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }
        
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere([
                'or',
                ['like', 'name', $search],
                ['like', 'nif', $search],
            ]);
        }
        
        // Contagem total
        $total = $query->count();
        
        // Paginação
        $page = max(1, (int)Yii::$app->request->get('page', 1));
        $perPage = min(100, max(1, (int)Yii::$app->request->get('per_page', 20)));
        $offset = ($page - 1) * $perPage;
        
        // Ordenação
        $sort = Yii::$app->request->get('sort', 'name');
        $order = Yii::$app->request->get('order', 'asc');
        $allowedSort = ['name', 'category', 'created_at'];
        
        if (in_array($sort, $allowedSort)) {
            $query->orderBy([$sort => $order === 'desc' ? SORT_DESC : SORT_ASC]);
        }
        
        $suppliers = $query->offset($offset)->limit($perPage)->all();
        
        return [
            'success' => true,
            'data' => array_map(function($supplier) {
                return $this->formatSupplier($supplier);
            }, $suppliers),
            'pagination' => [
                'total' => (int)$total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
            ],
        ];
    }
    
    /**
     * GET /api/suppliers/{id}
     */
    public function actionView($id)
    {
        $supplier = $this->findModel($id);
        
        return [
            'success' => true,
            'data' => $this->formatSupplier($supplier, true),
        ];
    }
    
    /**
     * POST /api/suppliers
     * 
     * Body:
     * {
     *   "name": "Nome do Fornecedor",
     *   "nif": "123456789",
     *   "email": "email@exemplo.com",
     *   "phone": "+351 XXX XXX XXX",
     *   "address": "Morada completa",
     *   "category": "parts",
     *   "notes": "Observações"
     * }
     */
    public function actionCreate()
    {
        $model = new Supplier();
        $model->company_id = Yii::$app->user->identity->company_id;
        
        $data = Yii::$app->request->getBodyParams();
        
        // Campos permitidos
        $allowedFields = ['name', 'nif', 'email', 'phone', 'address', 'category', 'status', 'notes'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $model->$field = $data[$field];
            }
        }
        
        if (!$model->validate()) {
            Yii::$app->response->statusCode = 422;
            return [
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $model->errors,
            ];
        }
        
        if (!$model->save()) {
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Erro ao guardar fornecedor',
            ];
        }
        
        Yii::$app->response->statusCode = 201;
        return [
            'success' => true,
            'message' => 'Fornecedor criado com sucesso',
            'data' => $this->formatSupplier($model),
        ];
    }
    
    /**
     * PUT /api/suppliers/{id}
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $data = Yii::$app->request->getBodyParams();
        
        $allowedFields = ['name', 'nif', 'email', 'phone', 'address', 'category', 'status', 'notes'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $model->$field = $data[$field];
            }
        }
        
        if (!$model->validate()) {
            Yii::$app->response->statusCode = 422;
            return [
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $model->errors,
            ];
        }
        
        if (!$model->save()) {
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Erro ao actualizar fornecedor',
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Fornecedor actualizado com sucesso',
            'data' => $this->formatSupplier($model),
        ];
    }
    
    /**
     * DELETE /api/suppliers/{id}
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if (!$model->delete()) {
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Erro ao eliminar fornecedor',
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Fornecedor eliminado com sucesso',
        ];
    }
    
    /**
     * GET /api/suppliers/stats
     * Estatísticas de fornecedores
     */
    public function actionStats()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $total = Supplier::find()->where(['company_id' => $companyId])->count();
        
        $active = Supplier::find()
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->count();
        
        $byCategory = Supplier::find()
            ->select(['category', 'COUNT(*) as count'])
            ->where(['company_id' => $companyId])
            ->groupBy(['category'])
            ->asArray()
            ->all();
        
        return [
            'success' => true,
            'data' => [
                'total' => (int)$total,
                'active' => (int)$active,
                'inactive' => (int)$total - (int)$active,
                'by_category' => $byCategory,
            ],
        ];
    }
    
    /**
     * Encontrar modelo com validação de empresa
     */
    protected function findModel($id)
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $model = Supplier::findOne([
            'id' => $id,
            'company_id' => $companyId,
        ]);
        
        if ($model === null) {
            throw new NotFoundHttpException('Fornecedor não encontrado');
        }
        
        return $model;
    }
    
    /**
     * Formatar fornecedor para resposta
     */
    protected function formatSupplier($supplier, $detailed = false)
    {
        $data = [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'nif' => $supplier->nif,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'category' => $supplier->category,
            'category_label' => $supplier->getCategoryLabel(),
            'status' => $supplier->status,
            'status_label' => $supplier->getStatusLabel(),
        ];
        
        if ($detailed) {
            $data['address'] = $supplier->address;
            $data['notes'] = $supplier->notes;
            $data['created_at'] = $supplier->created_at;
            $data['updated_at'] = $supplier->updated_at;
        }
        
        return $data;
    }
}
```

---

## Passo 2: Configurar Rotas

### Ficheiro: `backend/modules/api/Module.php`

```php
// No método init() ou em bootstrap
public function init()
{
    parent::init();
    
    // Adicionar regras de URL para o novo controller
    Yii::$app->urlManager->addRules([
        // Rotas para Supplier
        'GET api/suppliers' => 'api/supplier/index',
        'GET api/suppliers/stats' => 'api/supplier/stats',
        'GET api/suppliers/<id:\d+>' => 'api/supplier/view',
        'POST api/suppliers' => 'api/supplier/create',
        'PUT api/suppliers/<id:\d+>' => 'api/supplier/update',
        'DELETE api/suppliers/<id:\d+>' => 'api/supplier/delete',
    ], false);
}
```

### Alternativa: Em `backend/config/main.php`

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        // ... outras regras
        
        // Suppliers
        'GET api/suppliers' => 'api/supplier/index',
        'GET api/suppliers/stats' => 'api/supplier/stats',
        'GET api/suppliers/<id:\d+>' => 'api/supplier/view',
        'POST api/suppliers' => 'api/supplier/create',
        'PUT api/suppliers/<id:\d+>' => 'api/supplier/update',
        'DELETE api/suppliers/<id:\d+>' => 'api/supplier/delete',
    ],
],
```

---

## Passo 3: Testar Endpoints

### Com cURL

```bash
# Autenticar (obter token)
TOKEN=$(curl -s -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}' \
  | jq -r '.data.token')

# Listar fornecedores
curl -X GET "http://localhost/api/suppliers" \
  -H "Authorization: Bearer $TOKEN"

# Listar com filtros
curl -X GET "http://localhost/api/suppliers?category=parts&status=active&page=1&per_page=10" \
  -H "Authorization: Bearer $TOKEN"

# Ver fornecedor
curl -X GET "http://localhost/api/suppliers/1" \
  -H "Authorization: Bearer $TOKEN"

# Criar fornecedor
curl -X POST "http://localhost/api/suppliers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Auto Peças Lda",
    "nif": "123456789",
    "email": "geral@autopecas.pt",
    "phone": "+351 210 000 000",
    "category": "parts",
    "address": "Rua das Peças, 123, Lisboa"
  }'

# Actualizar fornecedor
curl -X PUT "http://localhost/api/suppliers/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+351 210 111 111",
    "status": "inactive"
  }'

# Eliminar fornecedor
curl -X DELETE "http://localhost/api/suppliers/1" \
  -H "Authorization: Bearer $TOKEN"

# Estatísticas
curl -X GET "http://localhost/api/suppliers/stats" \
  -H "Authorization: Bearer $TOKEN"
```

### Com JavaScript (Fetch)

```javascript
const API_URL = 'http://localhost/api';
const token = 'seu_token_aqui';

// Listar fornecedores
async function getSuppliers(filters = {}) {
    const params = new URLSearchParams(filters);
    const response = await fetch(`${API_URL}/suppliers?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`,
        },
    });
    return response.json();
}

// Criar fornecedor
async function createSupplier(data) {
    const response = await fetch(`${API_URL}/suppliers`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    });
    return response.json();
}

// Uso
const suppliers = await getSuppliers({ category: 'parts', status: 'active' });
const newSupplier = await createSupplier({
    name: 'Novo Fornecedor',
    nif: '123456789',
    category: 'general',
});
```

---

## Passo 4: Criar Testes

### Ficheiro: `api-tests/tests/test-suppliers.js`

```javascript
const HttpClient = require('../utils/http-client');
const assert = require('assert');

const client = new HttpClient('http://localhost');

async function runTests() {
    console.log('=== Testes de Suppliers ===\n');
    
    // 1. Login
    console.log('1. Login...');
    const loginRes = await client.post('/api/auth/login', {
        username: 'admin',
        password: 'admin123',
    });
    assert(loginRes.success, 'Login deve ter sucesso');
    const token = loginRes.data.token;
    client.setToken(token);
    console.log('✓ Login OK\n');
    
    // 2. Criar Fornecedor
    console.log('2. Criar fornecedor...');
    const createRes = await client.post('/api/suppliers', {
        name: 'Fornecedor Teste',
        nif: '999999999',
        email: 'teste@fornecedor.pt',
        category: 'parts',
    });
    assert(createRes.success, 'Criar deve ter sucesso');
    assert(createRes.data.id, 'Deve retornar ID');
    const supplierId = createRes.data.id;
    console.log(`✓ Fornecedor criado: ID ${supplierId}\n`);
    
    // 3. Listar
    console.log('3. Listar fornecedores...');
    const listRes = await client.get('/api/suppliers');
    assert(listRes.success, 'Listar deve ter sucesso');
    assert(Array.isArray(listRes.data), 'Data deve ser array');
    console.log(`✓ ${listRes.pagination.total} fornecedores encontrados\n`);
    
    // 4. Ver detalhe
    console.log('4. Ver fornecedor...');
    const viewRes = await client.get(`/api/suppliers/${supplierId}`);
    assert(viewRes.success, 'Ver deve ter sucesso');
    assert.strictEqual(viewRes.data.name, 'Fornecedor Teste');
    console.log('✓ Detalhe OK\n');
    
    // 5. Actualizar
    console.log('5. Actualizar fornecedor...');
    const updateRes = await client.put(`/api/suppliers/${supplierId}`, {
        name: 'Fornecedor Actualizado',
        status: 'inactive',
    });
    assert(updateRes.success, 'Actualizar deve ter sucesso');
    assert.strictEqual(updateRes.data.name, 'Fornecedor Actualizado');
    console.log('✓ Actualização OK\n');
    
    // 6. Estatísticas
    console.log('6. Estatísticas...');
    const statsRes = await client.get('/api/suppliers/stats');
    assert(statsRes.success, 'Stats deve ter sucesso');
    assert(typeof statsRes.data.total === 'number');
    console.log(`✓ Total: ${statsRes.data.total}, Activos: ${statsRes.data.active}\n`);
    
    // 7. Eliminar
    console.log('7. Eliminar fornecedor...');
    const deleteRes = await client.delete(`/api/suppliers/${supplierId}`);
    assert(deleteRes.success, 'Eliminar deve ter sucesso');
    console.log('✓ Eliminação OK\n');
    
    // 8. Verificar eliminação
    console.log('8. Verificar eliminação...');
    const verifyRes = await client.get(`/api/suppliers/${supplierId}`);
    assert(!verifyRes.success || verifyRes.data === null, 'Fornecedor não deve existir');
    console.log('✓ Fornecedor eliminado\n');
    
    console.log('=== Todos os testes passaram! ===');
}

runTests().catch(console.error);
```

---

## Documentação do Endpoint

### Adicionar a `API_ENDPOINTS_COMPLETE.md`

```markdown
## Suppliers (Fornecedores)

### Listar Fornecedores
```
GET /api/suppliers
```

**Query Parameters:**
| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| category | string | Filtrar por categoria |
| status | string | Filtrar por status |
| search | string | Pesquisar por nome ou NIF |
| page | int | Página (default: 1) |
| per_page | int | Items por página (default: 20) |
| sort | string | Campo para ordenação |
| order | string | asc ou desc |

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Auto Peças Lda",
      "nif": "123456789",
      "email": "geral@autopecas.pt",
      "phone": "+351 210 000 000",
      "category": "parts",
      "category_label": "Peças",
      "status": "active",
      "status_label": "Activo"
    }
  ],
  "pagination": {
    "total": 15,
    "page": 1,
    "per_page": 20,
    "total_pages": 1
  }
}
```

### Criar Fornecedor
```
POST /api/suppliers
```

**Body:**
```json
{
  "name": "Nome do Fornecedor",
  "nif": "123456789",
  "email": "email@exemplo.com",
  "phone": "+351 210 000 000",
  "address": "Morada completa",
  "category": "parts",
  "notes": "Observações"
}
```
```

---

## Checklist

- [ ] Model criado em `common/models/`
- [ ] Migration aplicada
- [ ] Controller API criado em `backend/modules/api/controllers/`
- [ ] Rotas configuradas
- [ ] Autenticação validada (extends BaseApiController)
- [ ] Multi-tenancy (filtro por company_id)
- [ ] Validações implementadas
- [ ] Testes criados
- [ ] Documentação actualizada

---

## Próximos Passos

- [Adicionar CRUD Frontend](adicionar-crud.md)
- [Testes](testes.md)
