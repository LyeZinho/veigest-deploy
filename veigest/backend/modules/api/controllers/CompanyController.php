<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\Company;

/**
 * Company API Controller
 * 
 * Fornece operações CRUD para empresas com controle de acesso
 * Implementa filtragem e validação de permissões
 * 
 * .
 */
class CompanyController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Company';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        // Customizar as ações padrão
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);

        return $actions;
    }

    /**
     * Lista todas as empresas (apenas admin)
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        // Verificar se usuário é admin
        if (!$this->hasPermission('company.view')) {
            throw new ForbiddenHttpException('Acesso negado. Permissão company.view necessária.');
        }

        $query = Company::find();
        
        // Filtros opcionais
        $request = Yii::$app->request;
        
        if ($status = $request->get('status')) {
            $query->andWhere(['status' => $status]);
        }
        
        if ($search = $request->get('search')) {
            $query->andWhere([
                'or',
                ['like', 'nome', $search],
                ['like', 'email', $search],
                ['like', 'tax_id', $search]
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $request->get('per-page', 20),
                'page' => $request->get('page', 1) - 1,
            ],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['id', 'nome', 'created_at', 'updated_at', 'status']
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Visualiza uma empresa específica
     * 
     * @param int $id
     * @return Company
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Verificar permissões
        if (!$this->hasPermission('company.view')) {
            // Usuários podem ver apenas sua própria empresa
            $userCompanyId = $this->getCompanyId();
            if (!$userCompanyId || $model->id != $userCompanyId) {
                throw new ForbiddenHttpException('Acesso negado à empresa.');
            }
        }

        return $model;
    }

    /**
     * Cria nova empresa (apenas admin)
     * 
     * @return Company
     */
    public function actionCreate()
    {
        if (!$this->hasPermission('company.create')) {
            throw new ForbiddenHttpException('Acesso negado. Permissão company.create necessária.');
        }

        $model = new Company();
        $model->load(Yii::$app->request->bodyParams, '');
        
        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        return $this->errorResponse('Erro ao criar empresa', 422, $model->errors);
    }

    /**
     * Atualiza uma empresa
     * 
     * @param int $id
     * @return Company
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        // Verificar permissões
        if (!$this->hasPermission('company.update')) {
            // Usuários podem atualizar apenas sua própria empresa
            $userCompanyId = $this->getCompanyId();
            if (!$userCompanyId || $model->id != $userCompanyId) {
                throw new ForbiddenHttpException('Acesso negado à atualização da empresa.');
            }
        }

        $model->load(Yii::$app->request->bodyParams, '');
        
        if ($model->save()) {
            return $model;
        }

        return $this->errorResponse('Erro ao atualizar empresa', 422, $model->errors);
    }

    /**
     * Lista veículos da empresa
     * 
     * @param int $id
     * @return ActiveDataProvider
     */
    public function actionVehicles($id)
    {
        $company = $this->findModel($id);
        
        // Verificar acesso
        $userCompanyId = $this->getCompanyId();
        if (!$this->hasPermission('company.view') && $company->id != $userCompanyId) {
            throw new ForbiddenHttpException('Acesso negado aos veículos da empresa.');
        }

        $query = $company->getVehicles();
        
        // Filtros opcionais
        $request = Yii::$app->request;
        if ($status = $request->get('status')) {
            $query->andWhere(['status' => $status]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $request->get('per-page', 20)],
        ]);
    }

    /**
     * Lista usuários da empresa
     * 
     * @param int $id
     * @return ActiveDataProvider
     */
    public function actionUsers($id)
    {
        $company = $this->findModel($id);
        
        // Verificar acesso
        $userCompanyId = $this->getCompanyId();
        if (!$this->hasPermission('company.view') && $company->id != $userCompanyId) {
            throw new ForbiddenHttpException('Acesso negado aos usuários da empresa.');
        }

        $query = $company->getUsers();
        
        // Filtros opcionais
        $request = Yii::$app->request;
        if ($tipo = $request->get('tipo')) {
            $query->andWhere(['tipo' => $tipo]);
        }
        if ($status = $request->get('status')) {
            $query->andWhere(['status' => $status]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $request->get('per-page', 20)],
        ]);
    }

    /**
     * Estatísticas da empresa
     * 
     * @param int $id
     * @return array
     */
    public function actionStats($id)
    {
        $company = $this->findModel($id);
        
        // Verificar acesso
        $userCompanyId = $this->getCompanyId();
        if (!$this->hasPermission('company.view') && $company->id != $userCompanyId) {
            throw new ForbiddenHttpException('Acesso negado às estatísticas da empresa.');
        }

        return [
            'company' => [
                'id' => $company->id,
                'nome' => $company->nome,
                'status' => $company->status,
                'created_at' => $company->created_at,
            ],
            'vehicles_count' => $company->getVehicles()->count(),
            'active_vehicles' => $company->getActiveVehiclesCount(),
            'users_count' => $company->getTotalUsersCount(),
            'drivers_count' => $company->getUsers()->where(['LIKE', 'roles', 'driver'])->count(),
            'maintenance_stats' => [
                'total_maintenances' => $company->getVehicles()
                    ->joinWith('maintenances')
                    ->count(),
                'pending_maintenances' => $company->getVehicles()
                    ->joinWith(['maintenances' => function($query) {
                        $query->where(['status' => 'scheduled']);
                    }])
                    ->count(),
            ],
            'fuel_stats' => [
                'total_fuel_logs' => $company->getVehicles()
                    ->joinWith('fuelLogs')
                    ->count(),
                'total_fuel_cost' => $company->getVehicles()
                    ->joinWith('fuelLogs')
                    ->sum('{{%fuel_logs}}.value') ?? 0,
            ],
        ];
    }

    /**
     * Busca modelo por ID
     * 
     * @param int $id
     * @return Company
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Company::findOne($id);
        
        if ($model === null) {
            throw new NotFoundHttpException('Empresa não encontrada');
        }

        return $model;
    }

    /**
     * Verifica se o usuário tem a permissão especificada
     * 
     * @param string $permission
     * @return bool
     */
    private function hasPermission($permission)
    {
        $user = Yii::$app->user->identity;
        if (!$user) {
            return false;
        }

        // Se é admin, tem todas as permissões
        if ($user->tipo === 'admin') {
            return true;
        }

        // Verificar permissão específica via RBAC
        return Yii::$app->authManager->checkAccess($user->id, $permission);
    }
}
