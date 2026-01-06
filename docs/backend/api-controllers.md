# 🔧 Controllers da API

## Visão Geral

Os controllers da API REST estão em `backend/modules/api/controllers/` e seguem o padrão RESTful do Yii2.

## Hierarquia de Controllers

```
yii\rest\ActiveController
         │
         ▼
  BaseApiController          # Controller base personalizado
         │
    ┌────┴────┬────────┬────────┬────────┬────────┐
    ▼         ▼        ▼        ▼        ▼        ▼
 Auth     Vehicle  Maintenance FuelLog  User   Company
Controller Controller Controller Controller Controller Controller
```

## BaseApiController

O controller base fornece funcionalidades comuns a todos os endpoints.

### Código Completo

```php
<?php
namespace backend\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\ContentNegotiator;
use yii\filters\VerbFilter;
use yii\filters\Cors;
use yii\web\Response;
use backend\modules\api\components\ApiAuthenticator;

class BaseApiController extends ActiveController
{
    /**
     * Configuração de behaviors (middlewares)
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // 1. Remover autenticador padrão
        unset($behaviors['authenticator']);

        // 2. CORS - Permitir requisições cross-origin
        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
            ],
        ];

        // 3. Negociação de conteúdo (JSON)
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // 4. Autenticação Bearer Token personalizada
        $behaviors['authenticator'] = [
            'class' => ApiAuthenticator::class,
            'except' => ['options'],  // OPTIONS não requer auth
        ];

        // 5. Filtro de verbos HTTP
        $behaviors['verbFilter'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index' => ['GET'],
                'view' => ['GET'],
                'create' => ['POST'],
                'update' => ['PUT', 'PATCH'],
                'delete' => ['DELETE'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Desabilitar ações padrão para customização
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * Responder a requisições OPTIONS (preflight CORS)
     */
    public function actionOptions()
    {
        Yii::$app->response->statusCode = 200;
        return [];
    }

    /**
     * Obter company_id do utilizador autenticado
     */
    protected function getCompanyId()
    {
        return Yii::$app->user->identity->company_id ?? null;
    }

    /**
     * Obter user_id do utilizador autenticado
     */
    protected function getUserId()
    {
        return Yii::$app->user->id ?? null;
    }

    /**
     * Resposta de erro padronizada
     */
    protected function errorResponse($message, $code = 400, $errors = [])
    {
        Yii::$app->response->statusCode = $code;
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ];
    }

    /**
     * Resposta de sucesso padronizada
     */
    protected function successResponse($data, $message = null, $code = 200)
    {
        Yii::$app->response->statusCode = $code;
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }
}
```

### Explicação dos Behaviors

| Behavior | Propósito |
|----------|-----------|
| `cors` | Permite requisições de outros domínios (AJAX) |
| `contentNegotiator` | Força respostas em JSON |
| `authenticator` | Valida Bearer Token |
| `verbFilter` | Mapeia métodos HTTP para actions |

---

## AuthController

Gestão de autenticação e tokens.

### Endpoints

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/auth/login` | Login e geração de token |
| POST | `/api/auth/logout` | Invalidar sessão |
| GET | `/api/auth/me` | Perfil do utilizador |
| POST | `/api/auth/refresh` | Renovar token |

### Código - actionLogin

```php
public function actionLogin()
{
    $request = Yii::$app->request;
    $username = $request->post('username');
    $password = $request->post('password');

    // Validar campos obrigatórios
    if (!$username || !$password) {
        return $this->errorResponse('Username e password são obrigatórios', 400);
    }

    // Buscar utilizador
    $user = User::findByUsername($username);
    if (!$user || !$user->validatePassword($password)) {
        return $this->errorResponse('Credenciais inválidas', 401);
    }

    // Verificar se está ativo
    if ($user->status != User::STATUS_ACTIVE) {
        return $this->errorResponse('Conta inativa', 403);
    }

    // Gerar token
    $tokenData = [
        'user_id' => $user->id,
        'company_code' => $user->company->code ?? 'DEFAULT',
        'username' => $user->username,
        'role' => $user->role,
        'expires_at' => time() + (24 * 60 * 60), // 24 horas
    ];
    $token = base64_encode(json_encode($tokenData));

    return $this->successResponse([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ],
        'expires_at' => $tokenData['expires_at'],
    ], 'Login realizado com sucesso');
}
```

---

## VehicleController

CRUD de veículos com multi-tenancy.

### Endpoints

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/vehicles` | Listar veículos |
| GET | `/api/vehicles/{id}` | Ver veículo |
| POST | `/api/vehicles` | Criar veículo |
| PUT | `/api/vehicles/{id}` | Atualizar veículo |
| DELETE | `/api/vehicles/{id}` | Remover veículo |
| GET | `/api/vehicles/{id}/maintenances` | Manutenções do veículo |
| GET | `/api/vehicles/{id}/fuel-logs` | Abastecimentos do veículo |
| GET | `/api/vehicles/{id}/stats` | Estatísticas do veículo |
| GET | `/api/vehicles/by-status/{status}` | Filtrar por estado |

### Código - actionIndex (com filtros)

```php
public function actionIndex()
{
    $companyId = $this->getCompanyId();
    $request = Yii::$app->request;

    // Query base com multi-tenancy
    $query = Vehicle::find()
        ->where(['company_id' => $companyId]);

    // Filtro por status
    if ($status = $request->get('status')) {
        $query->andWhere(['status' => $status]);
    }

    // Filtro por marca
    if ($brand = $request->get('brand')) {
        $query->andWhere(['like', 'brand', $brand]);
    }

    // Filtro por ano
    if ($year = $request->get('year')) {
        $query->andWhere(['year' => $year]);
    }

    // Ordenação
    $sort = $request->get('sort', 'created_at');
    $order = $request->get('order', 'DESC');
    $query->orderBy([$sort => $order === 'ASC' ? SORT_ASC : SORT_DESC]);

    // Paginação
    return new ActiveDataProvider([
        'query' => $query,
        'pagination' => [
            'pageSize' => $request->get('per_page', 20),
        ],
    ]);
}
```

### Código - actionCreate

```php
public function actionCreate()
{
    $model = new Vehicle();
    $model->company_id = $this->getCompanyId();
    
    if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
        Yii::$app->response->statusCode = 201;
        return $this->successResponse($model, 'Veículo criado com sucesso', 201);
    }
    
    return $this->errorResponse('Erro ao criar veículo', 400, $model->errors);
}
```

### Código - actionStats

```php
public function actionStats($id)
{
    $vehicle = $this->findModel($id);
    
    // Calcular estatísticas
    $totalMaintenance = Maintenance::find()
        ->where(['vehicle_id' => $id])
        ->sum('cost') ?? 0;
    
    $totalFuel = FuelLog::find()
        ->where(['vehicle_id' => $id])
        ->sum('total_cost') ?? 0;
    
    $avgConsumption = FuelLog::find()
        ->where(['vehicle_id' => $id])
        ->average('consumption') ?? 0;
    
    return [
        'vehicle' => $vehicle,
        'stats' => [
            'total_maintenance_cost' => (float) $totalMaintenance,
            'total_fuel_cost' => (float) $totalFuel,
            'total_cost' => (float) ($totalMaintenance + $totalFuel),
            'average_consumption' => round($avgConsumption, 2),
            'maintenance_count' => Maintenance::find()->where(['vehicle_id' => $id])->count(),
            'fuel_log_count' => FuelLog::find()->where(['vehicle_id' => $id])->count(),
        ],
    ];
}
```

---

## MaintenanceController

Gestão de manutenções.

### Endpoints Especiais

```php
// Agendar manutenção
public function actionSchedule($id)
{
    $model = $this->findModel($id);
    $request = Yii::$app->request;
    
    $model->next_date = $request->post('scheduled_date');
    $model->workshop = $request->post('workshop');
    
    if ($model->save()) {
        return $this->successResponse($model, 'Manutenção agendada');
    }
    
    return $this->errorResponse('Erro ao agendar', 400, $model->errors);
}

// Relatório mensal
public function actionReportsMonthly()
{
    $companyId = $this->getCompanyId();
    
    $data = Maintenance::find()
        ->select([
            'YEAR(date) as year',
            'MONTH(date) as month',
            'COUNT(*) as count',
            'SUM(cost) as total_cost',
        ])
        ->where(['company_id' => $companyId])
        ->groupBy(['YEAR(date)', 'MONTH(date)'])
        ->orderBy(['year' => SORT_DESC, 'month' => SORT_DESC])
        ->limit(12)
        ->asArray()
        ->all();
    
    return $data;
}
```

---

## FuelLogController

Gestão de abastecimentos.

### Código - actionEfficiencyReport

```php
public function actionEfficiencyReport()
{
    $companyId = $this->getCompanyId();
    
    $vehicles = Vehicle::find()
        ->where(['company_id' => $companyId])
        ->with(['fuelLogs'])
        ->all();
    
    $report = [];
    foreach ($vehicles as $vehicle) {
        $logs = $vehicle->fuelLogs;
        
        if (count($logs) < 2) {
            continue;
        }
        
        $totalLiters = array_sum(array_column($logs, 'liters'));
        $totalCost = array_sum(array_column($logs, 'total_cost'));
        $totalKm = $logs[count($logs) - 1]->mileage - $logs[0]->mileage;
        
        $report[] = [
            'vehicle_id' => $vehicle->id,
            'license_plate' => $vehicle->license_plate,
            'total_liters' => $totalLiters,
            'total_cost' => $totalCost,
            'total_km' => $totalKm,
            'km_per_liter' => $totalKm > 0 ? round($totalKm / $totalLiters, 2) : 0,
            'cost_per_km' => $totalKm > 0 ? round($totalCost / $totalKm, 2) : 0,
        ];
    }
    
    return $report;
}
```

---

## Método findModel (Padrão)

Todos os controllers implementam este método para buscar registos com validação:

```php
protected function findModel($id)
{
    $model = Vehicle::find()
        ->where(['id' => $id])
        ->andWhere(['company_id' => $this->getCompanyId()])  // Multi-tenancy!
        ->one();
    
    if ($model === null) {
        throw new NotFoundHttpException('Veículo não encontrado');
    }
    
    return $model;
}
```

## Próximos Passos

- [Models da API](api-models.md)
- [Autenticação](autenticacao.md)
- [Endpoints Completos](endpoints.md)
