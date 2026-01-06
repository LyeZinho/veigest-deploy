<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Driver;
use frontend\models\Vehicle;
use frontend\models\Route;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * DriverController - Driver Management
 * 
 * Access Control:
 * - Admin: NO ACCESS (frontend blocked)
 * - Manager: FULL ACCESS (view, create, update, delete)
 * - Driver: NO ACCESS (drivers management not visible to drivers)
 */
class DriverController extends Controller
{
    public $layout = 'dashboard';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // Block admin from frontend
                    [
                        'allow' => false,
                        'roles' => ['admin'],
                        'denyCallback' => function ($rule, $action) {
                            throw new ForbiddenHttpException(
                                'Administrators do not have access to the frontend.'
                            );
                        },
                    ],
                    // View drivers - manager only
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.view');
                        },
                    ],
                    // Create drivers - manager only
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.create');
                        },
                    ],
                    // Update drivers - manager only
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.update');
                        },
                    ],
                    // Delete drivers - manager only
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function($rule, $action) {
                            return Yii::$app->user->can('drivers.delete');
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lista de condutores
     * @return string
     */
    public function actionIndex()
    {
        // Filtra apenas utilizadores com a role 'driver' via RBAC
        $query = Driver::find()
            ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
            ->where(['company_id' => $this->getCompanyId()])
            ->andWhere(['auth_assignment.item_name' => 'driver']);
            // Drivers são identificados pela role 'driver' na tabela auth_assignment

        // Filtros opcionais
        $status = Yii::$app->request->get('status');
        if ($status !== null && $status !== '') {
            $query->andWhere(['status' => $status]);
        }

        $search = Yii::$app->request->get('search');
        if (!empty($search)) {
            $query->andWhere([
                'or',
                ['like', 'name', $search],
                ['like', 'email', $search],
                ['like', 'license_number', $search],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 15,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'name',
                    'email',
                    'status',
                    'license_expiry',
                    'created_at',
                ],
            ],
        ]);

        // Estatísticas para dashboard
        $stats = [
            'total' => Driver::find()
                ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
                ->where(['company_id' => $this->getCompanyId()])
                ->andWhere(['auth_assignment.item_name' => 'driver'])
                ->count(),
            'active' => Driver::find()
                ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
                ->where(['company_id' => $this->getCompanyId(), 'status' => Driver::STATUS_ACTIVE])
                ->andWhere(['auth_assignment.item_name' => 'driver'])
                ->count(),
            'inactive' => Driver::find()
                ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
                ->where(['company_id' => $this->getCompanyId(), 'status' => Driver::STATUS_INACTIVE])
                ->andWhere(['auth_assignment.item_name' => 'driver'])
                ->count(),
            'expiring_license' => Driver::find()
                ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
                ->where(['company_id' => $this->getCompanyId(), 'status' => Driver::STATUS_ACTIVE])
                ->andWhere(['auth_assignment.item_name' => 'driver'])
                ->andWhere(['<=', 'license_expiry', date('Y-m-d', strtotime('+30 days'))])
                ->andWhere(['>=', 'license_expiry', date('Y-m-d')])
                ->count(),
        ];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * Visualizar condutor com detalhes
     * @param int $id
     * @return string
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Veículos atribuídos a este condutor
        $vehiclesProvider = new ActiveDataProvider([
            'query' => Vehicle::find()
                ->where(['driver_id' => $model->id, 'company_id' => $this->getCompanyId()]),
            'pagination' => false,
        ]);

        // Rotas atribuídas (últimas 10)
        $routesProvider = new ActiveDataProvider([
            'query' => Route::find()
                ->where(['driver_id' => $model->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 10],
        ]);

        // Estatísticas do condutor
        $stats = [
            'total_vehicles' => $model->getVehicleCount(),
            'total_routes' => $model->getRouteCount(),
            // Removido filtro por status pois a tabela routes não tem essa coluna
            'completed_routes' => Route::find()
                ->where(['driver_id' => $model->id])
                ->andWhere(['not', ['end_time' => null]]) // Rotas concluídas = com end_time preenchido
                ->count(),
            'license_valid' => $model->isLicenseValid(),
            'days_until_license_expiry' => $model->getDaysUntilLicenseExpiry(),
        ];

        return $this->render('view', [
            'model' => $model,
            'vehiclesProvider' => $vehiclesProvider,
            'routesProvider' => $routesProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * Criar condutor
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Driver();
        $model->company_id = $this->getCompanyId();
        $model->status = Driver::STATUS_ACTIVE;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            // Definir roles como condutor
            $model->roles = 'condutor';

            // Set password se fornecida
            if (!empty($model->password)) {
                $model->setPassword($model->password);
            } else {
                // Gerar password aleatória se não fornecida
                $randomPassword = Yii::$app->security->generateRandomString(8);
                $model->setPassword($randomPassword);
            }

            // Auto-gerar username se não existir
            if (empty($model->username)) {
                $model->username = $this->generateUsername($model);
            }

            // auth_key para sessão/remember-me
            if (empty($model->auth_key)) {
                $model->auth_key = Yii::$app->security->generateRandomString();
            }

            if ($model->save()) {
                // Atribuir RBAC 'driver'
                $this->assignDriverRole($model->id);
                
                Yii::$app->session->setFlash('success', 'Condutor criado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error('Driver save failed: ' . json_encode($model->errors), 'driver-create');
                Yii::$app->session->setFlash('error', 'Erro ao criar condutor. Verifique os dados.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Atualizar condutor
     * @param int $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            // Atualizar password apenas se fornecida
            if (!empty($model->password)) {
                $model->setPassword($model->password);
            }

            if ($model->save()) {
                // Garantir atribuição RBAC
                $this->assignDriverRole($model->id);
                
                Yii::$app->session->setFlash('success', 'Condutor atualizado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error('Driver update failed: ' . json_encode($model->errors), 'driver-update');
                Yii::$app->session->setFlash('error', 'Erro ao atualizar condutor. Verifique os dados.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Apagar condutor (soft delete)
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Verificar se tem veículos atribuídos
        if ($model->getVehicleCount() > 0) {
            Yii::$app->session->setFlash('error', 'Não é possível apagar este condutor pois possui veículos atribuídos. Remova as atribuições primeiro.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Verificar se tem rotas pendentes
        $pendingRoutes = Route::find()
            ->where(['driver_id' => $model->id])
            ->andWhere(['in', 'status', ['pendente', 'em_curso']])
            ->count();
        
        if ($pendingRoutes > 0) {
            Yii::$app->session->setFlash('error', 'Não é possível apagar este condutor pois possui rotas pendentes ou em curso.');
            return $this->redirect(['view', 'id' => $id]);
        }

        // Soft delete - marcar como inativo
        $model->status = Driver::STATUS_INACTIVE;
        if ($model->save(false)) {
            // Remover role RBAC
            $auth = Yii::$app->authManager;
            $auth->revokeAll($model->id);
            
            Yii::$app->session->setFlash('success', 'Condutor desativado com sucesso.');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao desativar condutor.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Encontrar modelo por ID
     * @param int $id
     * @return Driver
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        // Buscar condutor que tenha role 'driver' no RBAC (auth_assignment)
        $model = Driver::find()
            ->innerJoin('auth_assignment', 'auth_assignment.user_id = users.id')
            ->where([
                'users.id' => $id,
                'users.company_id' => $this->getCompanyId(),
            ])
            ->andWhere(['auth_assignment.item_name' => 'driver'])
            ->one();

        if ($model !== null && $model->status != Driver::STATUS_INACTIVE) {
            return $model;
        }

        throw new NotFoundHttpException('Condutor não encontrado.');
    }

    /**
     * Obter ID da empresa do usuário logado
     * @return int|null
     */
    private function getCompanyId()
    {
        $identity = Yii::$app->user->identity;
        if ($identity instanceof \common\models\User) {
            return $identity->getAttribute('company_id');
        }
        return null;
    }

    /**
     * Gerar username único para o condutor
     * @param Driver $model
     * @return string
     */
    private function generateUsername($model)
    {
        $base = !empty($model->email) ? strstr($model->email, '@', true) : null;
        if (!$base) {
            $base = !empty($model->name) ? strtolower(preg_replace('/[^a-z0-9]+/i', '-', $model->name)) : 'condutor';
        }
        
        $username = $base;
        $suffix = 1;
        
        while (\common\models\User::find()->where(['username' => $username, 'company_id' => $model->company_id])->exists()) {
            $username = $base . '-' . $suffix++;
        }
        
        return $username;
    }

    /**
     * Atribuir role de driver no RBAC
     * @param int $userId
     */
    private function assignDriverRole($userId)
    {
        $auth = Yii::$app->authManager;
        
        // Verificar se já tem a role
        if (!$auth->getAssignment('driver', $userId)) {
            $role = $auth->getRole('driver');
            if ($role) {
                $auth->assign($role, $userId);
            }
        }
    }
}
