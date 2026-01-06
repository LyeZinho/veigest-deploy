# 🔐 Sistema de Autenticação

## Visão Geral

O VeiGest usa dois sistemas de autenticação:
1. **Frontend**: Sessões PHP (cookies)
2. **API REST**: Bearer Token (Base64)

---

## Autenticação Frontend (Sessões)

### Fluxo de Login

```
1. Utilizador acede /login
2. Preenche formulário (username + password)
3. POST para SiteController::actionLogin()
4. Validação contra base de dados
5. Criação de sessão PHP
6. Redirect para /dashboard
```

### Código - SiteController::actionLogin

```php
public function actionLogin()
{
    // Já autenticado? Redirecionar
    if (!Yii::$app->user->isGuest) {
        return $this->goHome();
    }

    $model = new LoginForm();
    
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        // Redirecionar conforme papel
        $user = Yii::$app->user->identity;
        
        switch ($user->role) {
            case 'admin':
            case 'gestor':
                return $this->redirect(['dashboard/index']);
            case 'condutor':
                return $this->redirect(['condutor/index']);
            default:
                return $this->goBack();
        }
    }

    $model->password = '';
    return $this->render('login', ['model' => $model]);
}
```

### Código - LoginForm (Model)

```php
<?php
namespace common\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Username ou password incorretos.');
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login(
                $this->getUser(),
                $this->rememberMe ? 3600 * 24 * 30 : 0  // 30 dias
            );
        }
        return false;
    }

    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }
}
```

### Código - User::validatePassword

```php
// common/models/User.php
public function validatePassword($password)
{
    return Yii::$app->security->validatePassword($password, $this->password_hash);
}

public function setPassword($password)
{
    $this->password_hash = Yii::$app->security->generatePasswordHash($password);
}

public function generateAuthKey()
{
    $this->auth_key = Yii::$app->security->generateRandomString();
}
```

### Verificar Autenticação nas Views

```php
// Verificar se está logado
if (!Yii::$app->user->isGuest) {
    echo 'Olá, ' . Yii::$app->user->identity->username;
}

// Obter dados do utilizador
$user = Yii::$app->user->identity;
$companyId = $user->company_id;
$role = $user->role;
```

### Proteção de Controllers (AccessControl)

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                // Permitir a utilizadores autenticados
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // Ou negar tudo (redireciona para login)
                [
                    'allow' => false,
                ],
            ],
        ],
    ];
}
```

---

## Autenticação API (Bearer Token)

### Fluxo de Autenticação

```
1. Cliente envia POST /api/auth/login com {username, password}
2. API valida credenciais
3. API gera token Base64 com dados do utilizador
4. Cliente armazena token
5. Cliente inclui token em todas as requisições
6. API valida token em cada requisição
```

### Estrutura do Token

```json
// Conteúdo decodificado
{
    "user_id": 1,
    "company_code": "VEI001",
    "username": "admin",
    "role": "admin",
    "expires_at": 1704067200
}

// Codificado em Base64
eyJ1c2VyX2lkIjoxLCJjb21wYW55X2NvZGUiOiJWRUkwMDEiLCJ1c2VybmFtZSI6ImFkbWluIiwicm9sZSI6ImFkbWluIiwiZXhwaXJlc19hdCI6MTcwNDA2NzIwMH0=
```

### Código - AuthController::actionLogin

```php
public function actionLogin()
{
    $request = Yii::$app->request;
    $username = $request->post('username');
    $password = $request->post('password');

    // Validação
    if (empty($username) || empty($password)) {
        Yii::$app->response->statusCode = 400;
        return [
            'success' => false,
            'message' => 'Username e password são obrigatórios',
        ];
    }

    // Buscar utilizador
    $user = User::findByUsername($username);
    
    if (!$user || !$user->validatePassword($password)) {
        Yii::$app->response->statusCode = 401;
        return [
            'success' => false,
            'message' => 'Credenciais inválidas',
        ];
    }

    // Verificar status
    if ($user->status !== User::STATUS_ACTIVE) {
        Yii::$app->response->statusCode = 403;
        return [
            'success' => false,
            'message' => 'Conta inativa ou bloqueada',
        ];
    }

    // Gerar token
    $expiresAt = time() + (24 * 60 * 60); // 24 horas
    
    $tokenData = [
        'user_id' => $user->id,
        'company_code' => $user->company->code ?? 'DEFAULT',
        'username' => $user->username,
        'role' => $user->role,
        'expires_at' => $expiresAt,
    ];
    
    $token = base64_encode(json_encode($tokenData));

    return [
        'success' => true,
        'message' => 'Login realizado com sucesso',
        'data' => [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
            'expires_in' => 86400, // segundos
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'company_id' => $user->company_id,
            ],
        ],
    ];
}
```

### Código - ApiAuthenticator

```php
<?php
namespace backend\modules\api\components;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;
use common\models\User;

class ApiAuthenticator extends AuthMethod
{
    public function authenticate($user, $request, $response)
    {
        // 1. Obter header Authorization
        $authHeader = $request->getHeaders()->get('Authorization');
        
        if (!$authHeader) {
            throw new UnauthorizedHttpException('Token de autenticação não fornecido');
        }
        
        // 2. Verificar formato Bearer
        if (!preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            throw new UnauthorizedHttpException('Formato de token inválido. Use: Bearer {token}');
        }
        
        $token = $matches[1];
        
        // 3. Decodificar Base64
        $decodedToken = base64_decode($token);
        
        if ($decodedToken === false) {
            throw new UnauthorizedHttpException('Token malformado');
        }
        
        // 4. Parse JSON
        $tokenData = json_decode($decodedToken, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnauthorizedHttpException('Token JSON inválido');
        }
        
        // 5. Validar estrutura
        if (!isset($tokenData['user_id']) || !isset($tokenData['expires_at'])) {
            throw new UnauthorizedHttpException('Token incompleto');
        }
        
        // 6. Verificar expiração
        if ($tokenData['expires_at'] < time()) {
            throw new UnauthorizedHttpException('Token expirado');
        }
        
        // 7. Buscar utilizador
        $identity = User::findOne([
            'id' => $tokenData['user_id'],
            'status' => User::STATUS_ACTIVE,
        ]);
        
        if (!$identity) {
            throw new UnauthorizedHttpException('Utilizador não encontrado ou inativo');
        }
        
        // 8. Login silencioso (sem sessão)
        $user->login($identity);
        
        return $identity;
    }
    
    public function challenge($response)
    {
        $response->headers->set('WWW-Authenticate', 'Bearer realm="api"');
    }
}
```

### Uso do Token pelo Cliente

```javascript
// JavaScript - Exemplo com fetch
const token = localStorage.getItem('authToken');

fetch('http://localhost:8002/api/vehicles', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
    },
})
.then(response => response.json())
.then(data => console.log(data));
```

```bash
# cURL - Exemplo
curl -X GET "http://localhost:8002/api/vehicles" \
    -H "Authorization: Bearer eyJ1c2VyX2lkIjox..." \
    -H "Content-Type: application/json"
```

```php
// PHP - Exemplo com Guzzle
$client = new GuzzleHttp\Client();
$response = $client->request('GET', 'http://localhost:8002/api/vehicles', [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json',
    ],
]);
```

---

## RBAC (Role-Based Access Control)

### Papéis Definidos

| Papel | Descrição | Permissões |
|-------|-----------|------------|
| `admin` | Administrador | Acesso total a todas as empresas |
| `gestor` | Gestor de Frota | Gestão completa da sua empresa |
| `condutor` | Condutor | Visualização do seu veículo |

### Verificação de Papel

```php
// No Controller
$user = Yii::$app->user->identity;

if ($user->role === 'admin') {
    // Acesso total
} elseif ($user->role === 'gestor') {
    // Filtrar por company_id
    $companyId = $user->company_id;
} else {
    // Condutor - apenas seu veículo
}

// Método auxiliar
public function checkAccess($action, $model = null, $params = [])
{
    $user = Yii::$app->user->identity;
    
    if ($user->role === 'admin') {
        return true;
    }
    
    // Verificar se pertence à mesma empresa
    if ($model && $model->company_id !== $user->company_id) {
        throw new ForbiddenHttpException('Sem permissão para aceder este recurso');
    }
    
    return true;
}
```

---

## Refresh Token

```php
public function actionRefresh()
{
    $user = Yii::$app->user->identity;
    
    // Gerar novo token
    $expiresAt = time() + (24 * 60 * 60);
    
    $tokenData = [
        'user_id' => $user->id,
        'company_code' => $user->company->code ?? 'DEFAULT',
        'username' => $user->username,
        'role' => $user->role,
        'expires_at' => $expiresAt,
    ];
    
    $newToken = base64_encode(json_encode($tokenData));
    
    return [
        'success' => true,
        'data' => [
            'token' => $newToken,
            'expires_at' => $expiresAt,
        ],
    ];
}
```

---

## Sistema RBAC - Controle de Acesso por Role

### Roles Disponíveis

| Role | Descrição | Frontend | Backend |
|------|-----------|----------|---------|
| `admin` | Administrador do Sistema | ❌ Bloqueado | ✅ Acesso Total |
| `manager` | Gestor de Frota | ✅ Acesso Total | ❌ Bloqueado |
| `driver` | Condutor | ✅ Leitura Apenas | ❌ Bloqueado |

### Verificação de Role

```php
// Obter role do usuário atual
$role = Yii::$app->user->identity->role;

// Verificar role específica
if ($role === 'admin') {
    // Código para admin
} elseif ($role === 'manager') {
    // Código para manager  
} elseif ($role === 'driver') {
    // Código para driver
}

// Usar método hasRole do User model
if (Yii::$app->user->identity->hasRole('manager')) {
    // É manager
}
```

### Padrão RBAC nos Controllers

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                // 1. Bloquear admin do frontend
                [
                    'allow' => false,
                    'roles' => ['admin'],
                    'denyCallback' => function ($rule, $action) {
                        throw new ForbiddenHttpException('Admin não tem acesso ao frontend.');
                    },
                ],
                
                // 2. Permitir view para quem tem permissão
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->can('module.view');
                    },
                ],
                
                // 3. CRUD apenas para manager
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete'],
                    'matchCallback' => function ($rule, $action) {
                        return Yii::$app->user->can('module.create');
                    },
                ],
            ],
        ],
    ];
}
```

### Permissões RBAC Disponíveis

```
// Vehicles
vehicles.view, vehicles.create, vehicles.update, vehicles.delete, vehicles.assign

// Drivers  
drivers.view, drivers.create, drivers.update, drivers.delete

// Routes
routes.view, routes.create, routes.update, routes.delete

// Maintenance
maintenances.view, maintenances.create, maintenances.update, maintenances.delete, maintenances.schedule

// Documents
documents.view, documents.create, documents.update, documents.delete

// Fuel
fuel.view, fuel.create, fuel.update, fuel.delete

// Alerts
alerts.view, alerts.create, alerts.resolve

// Reports
reports.view, reports.create, reports.export, reports.advanced

// Dashboard
dashboard.view, dashboard.advanced
```

### Esconder Elementos na View por Role

```php
<?php 
$userRole = Yii::$app->user->identity->role ?? null;
$isManager = ($userRole === 'manager');
$isDriver = ($userRole === 'driver');
?>

<!-- Botão visível apenas para manager -->
<?php if ($isManager): ?>
    <?= Html::a('Create New', ['create'], ['class' => 'btn btn-success']) ?>
<?php endif; ?>

<!-- Botão visível para todos -->
<?= Html::a('View Details', ['view', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
```

---

## Próximos Passos

- [Endpoints Completos](endpoints.md)
- [Erros Comuns](../troubleshooting/erros-comuns.md)
