# 🎮 Controllers do Frontend

## Visão Geral

Os controllers do frontend gerem páginas web e estão em `frontend/controllers/`. Cada controller tem um layout associado e actions que renderizam views.

## Controllers Disponíveis

| Controller | Layout | Responsabilidade |
|------------|--------|------------------|
| `SiteController` | main | Páginas públicas, login, registo |
| `DashboardController` | dashboard | Dashboard principal, métricas |
| `ProfileController` | dashboard | Gestão de perfil pessoal (RF-FO-003) |
| `ReportController` | dashboard | Relatórios e análises |
| `DocumentController` | dashboard | Gestão documental |
| `DriverController` | dashboard | Gestão de condutores |
| `VehicleController` | dashboard | Gestão de veículos |
| `MaintenanceController` | dashboard | Gestão de manutenções |
| `RouteController` | dashboard | Gestão de rotas |
| `AlertController` | dashboard | Gestão de alertas |

---

## SiteController

Gere páginas públicas e autenticação.

### Estrutura

```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\LoginForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

class SiteController extends Controller
{
    // Layout para páginas públicas
    public $layout = 'main';
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],  // Apenas autenticados
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
    
    // Páginas públicas
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    public function actionContact()
    {
        $model = new ContactForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Mensagem enviada!');
            }
            return $this->refresh();
        }
        
        return $this->render('contact', ['model' => $model]);
    }
    
    // Autenticação
    public function actionLogin()
    {
        // Força layout de login
        $this->layout = 'login';
        
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Redirecionar conforme papel
            $role = Yii::$app->user->identity->role;
            
            if (in_array($role, ['admin', 'gestor'])) {
                return $this->redirect(['dashboard/index']);
            }
            return $this->redirect(['condutor/index']);
        }

        return $this->render('login', ['model' => $model]);
    }
    
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
    
    public function actionSignup()
    {
        $this->layout = 'login';
        
        $model = new SignupForm();
        
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Conta criada com sucesso!');
            return $this->redirect(['site/login']);
        }

        return $this->render('signup', ['model' => $model]);
    }
    
    // Página de erro
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        
        if ($exception !== null) {
            return $this->render('error', [
                'exception' => $exception,
                'name' => $exception->getName(),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
```

### Actions Disponíveis

| Action | URL | Descrição |
|--------|-----|-----------|
| `actionIndex` | `/` | Homepage |
| `actionLogin` | `/site/login` | Página de login |
| `actionLogout` | `/site/logout` | Logout (POST) |
| `actionSignup` | `/site/signup` | Registo |
| `actionContact` | `/site/contact` | Formulário de contacto |
| `actionAbout` | `/site/about` | Sobre nós |
| `actionError` | - | Página de erro |
| `actionServices` | `/site/services` | Serviços |
| `actionPricing` | `/site/pricing` | Preços |

---

## DashboardController

Dashboard principal com métricas e gráficos.

### Estrutura

```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\Vehicle;
use common\models\Maintenance;
use common\models\FuelLog;
use common\models\Alert;
use common\models\User;

class DashboardController extends Controller
{
    public $layout = 'dashboard';
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],  // Apenas autenticados
                    ],
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        // KPIs
        $totalVehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->count();
        
        $totalDrivers = User::find()
            ->where(['company_id' => $companyId, 'role' => 'condutor'])
            ->count();
        
        $activeAlerts = Alert::find()
            ->where(['company_id' => $companyId, 'status' => 'active'])
            ->count();
        
        // Custo mensal de manutenção
        $startOfMonth = date('Y-m-01');
        $monthlyCost = Maintenance::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'date', $startOfMonth])
            ->sum('cost') ?? 0;
        
        // Dados para gráfico de combustível (últimos 12 meses)
        $fuelMonthly = FuelLog::find()
            ->select([
                'DATE_FORMAT(date, "%Y-%m") as month_label',
                'SUM(liters) as total_liters',
            ])
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'date', date('Y-m-d', strtotime('-12 months'))])
            ->groupBy(['month_label'])
            ->orderBy(['month_label' => SORT_ASC])
            ->asArray()
            ->all();
        
        // Estado da frota
        $fleetState = Vehicle::find()
            ->select(['status', 'COUNT(*) as count'])
            ->where(['company_id' => $companyId])
            ->groupBy(['status'])
            ->indexBy('status')
            ->column();
        
        // Alertas recentes
        $recentAlerts = Alert::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();
        
        return $this->render('index', [
            'totalVehicles' => $totalVehicles,
            'totalDrivers' => $totalDrivers,
            'activeAlerts' => $activeAlerts,
            'monthlyCost' => $monthlyCost,
            'fuelMonthly' => $fuelMonthly,
            'fleetState' => $fleetState,
            'recentAlerts' => $recentAlerts,
        ]);
    }
    
    // Outras pages do dashboard - Redirecionam para controllers específicos
    public function actionVehicles()
    {
        // Redireciona para VehicleController::actionIndex
        return $this->redirect(['vehicle/index']);
    }
    
    public function actionMaintenance($status = 'scheduled')
    {
        // Redireciona para MaintenanceController::actionIndex
        return $this->redirect(['maintenance/index', 'status' => $status]);
    }
    
    public function actionDrivers()
    {
        // Redireciona para DriverController::actionIndex
        return $this->redirect(['driver/index']);
    }
    
    public function actionDocuments()
    {
        // Redireciona para DocumentController::actionIndex
        return $this->redirect(['document/index']);
    }
    
    public function actionAlerts()
    {
        // Redireciona para AlertController::actionIndex
        return $this->redirect(['alert/index']);
    }
    
    public function actionReports()
    {
        // Redireciona para ReportController::actionIndex
        return $this->redirect(['report/index']);
    }
}
```

### Nota sobre Rotas

O `DashboardController` atua como um **facade** para manter compatibilidade com URLs antigas.
As rotas `dashboard/vehicles`, `dashboard/drivers`, etc. redirecionam para os controllers específicos:

| URL Antiga | Redireciona Para |
|------------|-----------------|
| `dashboard/vehicles` | `vehicle/index` |
| `dashboard/drivers` | `driver/index` |
| `dashboard/maintenance` | `maintenance/index` |
| `dashboard/documents` | `document/index` |
| `dashboard/alerts` | `alert/index` |
| `dashboard/reports` | `report/index` |

---

## ReportController

Gera relatórios e análises.

### Estrutura

```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\Vehicle;
use common\models\Maintenance;
use common\models\FuelLog;
use common\models\Document;

class ReportController extends Controller
{
    public $layout = 'dashboard';
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $period = Yii::$app->request->get('period', 'month');
        
        // Estatísticas gerais
        $stats = $this->getOverviewStats($companyId, $period);
        
        // Dados para gráficos
        $fuelMonthly = $this->getFuelMonthlyData($companyId);
        $maintenanceMonthly = $this->getMaintenanceMonthlyData($companyId);
        $maintenanceByCategory = Maintenance::getCostsByType($companyId);
        
        // Documentos
        $documentStats = $this->getDocumentStats($companyId);
        
        // Veículos para filtro
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->all();
        
        return $this->render('index', [
            'stats' => $stats,
            'fuelMonthly' => $fuelMonthly,
            'maintenanceMonthly' => $maintenanceMonthly,
            'maintenanceByCategory' => $maintenanceByCategory,
            'documentStats' => $documentStats,
            'vehicles' => $vehicles,
            'period' => $period,
        ]);
    }
    
    public function actionVehicles()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $period = Yii::$app->request->get('period', 'month');
        
        // Análise por veículo
        $vehiclesAnalysis = $this->getVehiclesAnalysis($companyId, $period);
        
        return $this->render('vehicles', [
            'vehiclesAnalysis' => $vehiclesAnalysis,
            'period' => $period,
        ]);
    }
    
    public function actionMaintenance()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $maintenanceStats = Maintenance::getStatsByCompany($companyId);
        $maintenanceMonthly = Maintenance::getMonthlyCosts($companyId, 12);
        $maintenanceByType = Maintenance::getCostsByType($companyId);
        $maintenanceByVehicle = Maintenance::getCostsByVehicle($companyId);
        $upcomingMaintenance = Maintenance::getUpcoming($companyId, 30);
        
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->all();
        
        return $this->render('maintenance', [
            'maintenanceStats' => $maintenanceStats,
            'maintenanceMonthly' => $maintenanceMonthly,
            'maintenanceByType' => $maintenanceByType,
            'maintenanceByVehicle' => $maintenanceByVehicle,
            'upcomingMaintenance' => $upcomingMaintenance,
            'vehicles' => $vehicles,
        ]);
    }
    
    public function actionFuel()
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        // Estatísticas de combustível
        $fuelStats = $this->getFuelStats($companyId);
        $fuelMonthly = $this->getFuelMonthlyData($companyId);
        $fuelByVehicle = $this->getFuelByVehicle($companyId);
        
        // Últimos abastecimentos
        $recentFuelLogs = FuelLog::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['date' => SORT_DESC])
            ->limit(20)
            ->all();
        
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->all();
        
        return $this->render('fuel', [
            'fuelStats' => $fuelStats,
            'fuelMonthly' => $fuelMonthly,
            'fuelByVehicle' => $fuelByVehicle,
            'recentFuelLogs' => $recentFuelLogs,
            'vehicles' => $vehicles,
        ]);
    }
    
    // Métodos auxiliares privados
    private function getOverviewStats($companyId, $period)
    {
        // ... implementação
    }
    
    private function getFuelMonthlyData($companyId)
    {
        return FuelLog::find()
            ->select([
                'DATE_FORMAT(date, "%Y-%m") as month',
                'SUM(liters) as total_liters',
                'SUM(total_cost) as total_cost',
            ])
            ->where(['company_id' => $companyId])
            ->groupBy(['month'])
            ->orderBy(['month' => SORT_ASC])
            ->asArray()
            ->all();
    }
}
```

### Actions Disponíveis

| Action | URL | Descrição |
|--------|-----|-----------|
| `actionIndex` | `/report/index` | Relatório geral |
| `actionVehicles` | `/report/vehicles` | Análise de veículos |
| `actionMaintenance` | `/report/maintenance` | Relatório de manutenção |
| `actionFuel` | `/report/fuel` | Relatório de combustível |

---

## DocumentController

Gestão de documentos com upload.

### Estrutura

```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Document;
use common\models\File;
use common\models\Vehicle;
use frontend\models\DocumentSearch;
use frontend\models\DocumentUploadForm;

class DocumentController extends Controller
{
    public $layout = 'dashboard';
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // Estatísticas
        $companyId = Yii::$app->user->identity->company_id;
        $stats = [
            'total' => Document::find()->where(['company_id' => $companyId])->count(),
            'valid' => Document::find()->where(['company_id' => $companyId, 'status' => 'valid'])->count(),
            'expired' => Document::find()->where(['company_id' => $companyId, 'status' => 'expired'])->count(),
        ];
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
        ]);
    }
    
    public function actionCreate()
    {
        $model = new DocumentUploadForm();
        
        // Dados para dropdowns
        $companyId = Yii::$app->user->identity->company_id;
        $vehicles = Vehicle::find()->where(['company_id' => $companyId])->all();
        $drivers = User::find()->where(['company_id' => $companyId, 'role' => 'condutor'])->all();
        
        if ($model->load(Yii::$app->request->post())) {
            $model->uploadedFile = UploadedFile::getInstance($model, 'uploadedFile');
            
            if ($model->upload()) {
                Yii::$app->session->setFlash('success', 'Documento carregado com sucesso');
                return $this->redirect(['index']);
            }
        }
        
        return $this->render('create', [
            'model' => $model,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }
    
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model' => $model]);
    }
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        return $this->render('update', ['model' => $model]);
    }
    
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }
    
    public function actionDownload($id)
    {
        $model = $this->findModel($id);
        $file = $model->file;
        
        if (!$file || !file_exists($file->path)) {
            throw new NotFoundHttpException('Ficheiro não encontrado');
        }
        
        return Yii::$app->response->sendFile($file->path, $file->original_name);
    }
    
    protected function findModel($id)
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $model = Document::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();
        
        if ($model === null) {
            throw new NotFoundHttpException('Documento não encontrado');
        }
        
        return $model;
    }
}
```

---

## Padrões Comuns

### Filtrar por Empresa (Multi-Tenancy)

```php
$companyId = Yii::$app->user->identity->company_id;

$query = Model::find()->where(['company_id' => $companyId]);
```

### Flash Messages

```php
// No controller
Yii::$app->session->setFlash('success', 'Operação realizada!');
Yii::$app->session->setFlash('error', 'Ocorreu um erro');

// Na view (via widget Alert)
<?= Alert::widget() ?>
```

### Redireccionamentos

```php
return $this->redirect(['action']);           // Mesma controller
return $this->redirect(['controller/action']); // Outra controller
return $this->redirect(['action', 'id' => 1]); // Com parâmetros
return $this->goBack();                        // Página anterior
return $this->goHome();                        // Homepage
return $this->refresh();                       // Mesma página
```

---

## ProfileController

Gere o perfil pessoal do utilizador (RF-FO-003).

### Actions

| Action | Rota | Descrição |
|--------|------|-----------|
| `index` | `/profile` | Visualização do perfil |
| `update` | `/profile/update` | Edição de dados |
| `changePassword` | `/profile/change-password` | Alteração de senha |
| `history` | `/profile/history` | Histórico de alterações |
| `deletePhoto` | `/profile/delete-photo` | Remover foto (POST) |

### Estrutura

```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use frontend\models\ProfileForm;
use frontend\models\ChangePasswordForm;
use common\models\ProfileHistory;

class ProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']], // Apenas autenticados
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete-photo' => ['POST']],
            ],
        ];
    }

    // RF-FO-003.1: Visualização de dados pessoais
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        
        $historyProvider = new ActiveDataProvider([
            'query' => ProfileHistory::find()
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', [
            'user' => $user,
            'historyProvider' => $historyProvider,
        ]);
    }

    // RF-FO-003.2 + RF-FO-003.4: Edição + Upload de foto
    public function actionUpdate()
    {
        $model = new ProfileForm();
        $model->loadFromUser(Yii::$app->user->identity);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->photoFile = UploadedFile::getInstance($model, 'photoFile');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Perfil atualizado.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    // RF-FO-003.3: Alteração de palavra-passe
    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Senha alterada.');
            return $this->redirect(['index']);
        }

        return $this->render('change-password', ['model' => $model]);
    }

    // RF-FO-003.5: Histórico de alterações
    public function actionHistory()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ProfileHistory::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('history', ['dataProvider' => $dataProvider]);
    }
}
```

> 📖 **Documentação completa:** Ver [Sistema de Perfil](profile.md)

## Próximos Passos

- [Views e Templates](views.md)
- [Layouts](layouts.md)
