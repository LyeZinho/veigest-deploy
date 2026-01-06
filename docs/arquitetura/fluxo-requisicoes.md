# 🔄 Fluxo de Requisições

## Visão Geral

Este documento explica como as requisições HTTP são processadas no VeiGest, desde a entrada até a resposta.

## Fluxo Frontend (Interface Web)

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Browser   │────▶│  index.php  │────▶│   Router    │────▶│ Controller  │
└─────────────┘     └─────────────┘     └─────────────┘     └──────┬──────┘
                                                                    │
                    ┌─────────────┐     ┌─────────────┐             │
                    │    View     │◀────│    Model    │◀────────────┘
                    └──────┬──────┘     └─────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │    HTML     │
                    └─────────────┘
```

### Passo a Passo

#### 1. Entrada (`frontend/web/index.php`)

```php
<?php
// Definir constantes de ambiente
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// Carregar autoloader e Yii
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';

// Carregar configurações
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

// Mesclar configurações
$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

// Criar e executar aplicação
(new yii\web\Application($config))->run();
```

#### 2. Roteamento (`frontend/config/main.php`)

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        '' => 'site/index',
        'login' => 'site/login',
        'dashboard' => 'dashboard/index',
        '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
    ],
],
```

**Exemplos de Roteamento:**
| URL | Controller | Action |
|-----|------------|--------|
| `/` | SiteController | actionIndex |
| `/login` | SiteController | actionLogin |
| `/dashboard` | DashboardController | actionIndex |
| `/report/vehicles` | ReportController | actionVehicles |
| `?r=dashboard/index` | DashboardController | actionIndex |

#### 3. Controller Processa Requisição

```php
// frontend/controllers/DashboardController.php
class DashboardController extends Controller
{
    public $layout = 'dashboard';  // Usa layout específico
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],  // Apenas utilizadores autenticados
                    ],
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {
        // 1. Obter dados do model
        $totalVehicles = Vehicle::find()
            ->where(['company_id' => Yii::$app->user->identity->company_id])
            ->count();
        
        // 2. Passar dados para a view
        return $this->render('index', [
            'totalVehicles' => $totalVehicles,
            'totalDrivers' => $totalDrivers,
            // ...
        ]);
    }
}
```

#### 4. View Renderiza HTML

```php
<!-- frontend/views/dashboard/index.php -->
<?php
/** @var yii\web\View $this */
$this->title = 'Dashboard';
?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <h3><?= $totalVehicles ?></h3>
            <p>Total de Veículos</p>
        </div>
    </div>
</div>
```

#### 5. Layout Envolve a View

```php
<!-- frontend/views/layouts/dashboard.php -->
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    
    <!-- Sidebar -->
    <aside>...</aside>
    
    <!-- Conteúdo principal -->
    <main>
        <?= $content ?>  <!-- View é inserida aqui -->
    </main>
    
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

---

## Fluxo API REST (Backend)

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Cliente   │────▶│  index.php  │────▶│   Router    │────▶│ API Module  │
└─────────────┘     └─────────────┘     └─────────────┘     └──────┬──────┘
      ▲                                                            │
      │             ┌─────────────┐     ┌─────────────┐            │
      │             │    JSON     │◀────│ Controller  │◀───────────┘
      │             └──────┬──────┘     └──────┬──────┘
      │                    │                   │
      └────────────────────┘            ┌──────┴──────┐
                                        │    Model    │
                                        └─────────────┘
```

### Passo a Passo API

#### 1. Requisição Chega com Token

```http
GET /api/vehicles HTTP/1.1
Host: localhost:8002
Authorization: Bearer eyJ1c2VyX2lkIjoxLCJjb21wYW55X2NvZGUiOiJWRUkwMDEi...
Content-Type: application/json
```

#### 2. Módulo API Inicializa

```php
// backend/modules/api/Module.php
class Module extends BaseModule
{
    public $controllerNamespace = 'backend\modules\api\controllers';
    
    public function init()
    {
        parent::init();
        
        // Força resposta JSON
        \Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Desabilita sessões (stateless)
        \Yii::$app->user->enableSession = false;
        
        // Configura CORS
        \Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function ($event) {
            $response = $event->sender;
            $response->headers->add('Access-Control-Allow-Origin', '*');
            $response->headers->add('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
            $response->headers->add('Access-Control-Allow-Headers', 'Authorization, Content-Type');
        });
    }
}
```

#### 3. Autenticador Valida Token

```php
// backend/modules/api/components/ApiAuthenticator.php
class ApiAuthenticator extends AuthMethod
{
    public function authenticate($user, $request, $response)
    {
        // Extrair header Authorization
        $authHeader = $request->getHeaders()->get('Authorization');
        
        // Verificar formato Bearer
        if (!preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            throw new UnauthorizedHttpException('Token inválido');
        }
        
        $token = $matches[1];
        
        // Decodificar Base64
        $decodedToken = base64_decode($token);
        $tokenData = json_decode($decodedToken, true);
        
        // Validar expiração
        if ($tokenData['expires_at'] < time()) {
            throw new UnauthorizedHttpException('Token expirado');
        }
        
        // Buscar utilizador
        $identity = User::findOne($tokenData['user_id']);
        
        // Login silencioso
        $user->login($identity);
        
        return $identity;
    }
}
```

#### 4. Controller Processa e Retorna JSON

```php
// backend/modules/api/controllers/VehicleController.php
class VehicleController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Vehicle';
    
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        $query = Vehicle::find()
            ->where(['company_id' => $companyId]);
        
        // Aplicar filtros
        if ($status = Yii::$app->request->get('status')) {
            $query->andWhere(['status' => $status]);
        }
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);
    }
    
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }
}
```

#### 5. Resposta JSON

```json
{
    "items": [
        {
            "id": 1,
            "license_plate": "AA-00-BB",
            "brand": "Toyota",
            "model": "Hilux",
            "status": "active"
        }
    ],
    "pagination": {
        "totalCount": 15,
        "pageCount": 1,
        "currentPage": 1,
        "perPage": 20
    }
}
```

---

## Ciclo de Vida de uma Action

```php
// Ordem de execução
1. beforeAction()           // Verificações pré-execução
2. behaviors()              // AccessControl, VerbFilter, etc.
3. actionNome()             // Lógica principal
4. afterAction()            // Pós-processamento
5. render() / return        // Resposta
```

## Diagrama de Sequência - Login

```
Browser              Frontend              API                  DB
   │                    │                   │                   │
   │──GET /login───────▶│                   │                   │
   │◀──Form HTML────────│                   │                   │
   │                    │                   │                   │
   │──POST credentials─▶│                   │                   │
   │                    │──POST /api/auth/login─────────────────▶
   │                    │                   │──SELECT user──────▶
   │                    │                   │◀──user data───────│
   │                    │◀──{token, user}───│                   │
   │◀──Set cookie + redirect────────────────│                   │
   │                    │                   │                   │
   │──GET /dashboard───▶│                   │                   │
   │                    │──Bearer token────▶│                   │
   │                    │                   │──validate─────────▶
   │                    │◀──dashboard data──│                   │
   │◀──HTML dashboard───│                   │                   │
```

## Próximos Passos

- [Controllers da API](../backend/api-controllers.md)
- [Autenticação](../backend/autenticacao.md)
