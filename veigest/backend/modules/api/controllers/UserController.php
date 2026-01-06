<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use common\models\User;

/**
 * User API Controller
 * 
 * Fornece operações CRUD para usuários/condutores com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * .
 */
class UserController extends BaseApiController
{
    public $modelClass = 'common\models\User';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        // Customizar as ações para aplicar filtros de empresa
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);

        return $actions;
    }

    /**
     * Lista todos os usuários da empresa do usuário autenticado
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Empresa não identificada no token');
        }

        $query = User::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['status' => 'active']);

        // Filtros opcionais
        $request = Yii::$app->request;
        
        if ($role = $request->get('role')) {
            $query->andWhere(['like', 'roles', $role]);
        }
        
        if ($status = $request->get('status')) {
            $query->andWhere(['status' => $status]);
        }

        if ($search = $request->get('search')) {
            $query->andWhere(['or',
                ['like', 'name', $search],
                ['like', 'username', $search],
                ['like', 'email', $search],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC]
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Cria novo usuário
     * Automaticamente associa à empresa do usuário autenticado
     * 
     * @return User
     */
    public function actionCreate()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Empresa não identificada no token');
        }

        $model = new User();
        $model->scenario = 'create';
        $model->load(Yii::$app->request->bodyParams, '');
        $model->company_id = $companyId; // Forçar company_id do token
        $model->status = 'active';
        
        // Gerar auth_key se não fornecido
        if (!$model->auth_key) {
            $model->generateAuthKey();
        }

        // Hash da password
        if ($model->password) {
            $model->setPassword($model->password);
        }

        if ($model->save()) {
            Yii::$app->response->statusCode = 201;
            return $this->successResponse($model, 'Usuário criado com sucesso', 201);
        }

        return $this->errorResponse('Erro ao criar usuário', 400, $model->errors);
    }

    /**
     * Atualiza usuário existente
     * Verifica se pertence à empresa do usuário autenticado
     * 
     * @param integer $id
     * @return User
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        $model->scenario = 'update';
        $model->load(Yii::$app->request->bodyParams, '');
        
        // Não permitir alteração de company_id
        $model->company_id = $this->getCompanyId();

        // Hash da nova password se fornecida
        $bodyParams = Yii::$app->request->bodyParams;
        if (isset($bodyParams['password']) && !empty($bodyParams['password'])) {
            $model->setPassword($bodyParams['password']);
        }

        if ($model->save()) {
            return $this->successResponse($model, 'Usuário atualizado com sucesso');
        }

        return $this->errorResponse('Erro ao atualizar usuário', 400, $model->errors);
    }

    /**
     * Listar apenas condutores
     * 
     * @return ActiveDataProvider
     */
    public function actionDrivers()
    {
        $companyId = $this->getCompanyId();
        
        if (!$companyId) {
            throw new ForbiddenHttpException('Empresa não identificada no token');
        }

        $query = User::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['status' => 'active'])
            ->andWhere(['like', 'roles', 'driver']);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => ['defaultOrder' => ['name' => SORT_ASC]],
        ]);
    }

    /**
     * Obter perfil do usuário autenticado
     * 
     * @return array
     */
    public function actionProfile()
    {
        $user = Yii::$app->user->identity;
        $tokenData = Yii::$app->params['token_data'] ?? [];
        
        $company = null;
        if ($user->company_id) {
            $company = \backend\modules\api\models\Company::findOne($user->company_id);
        }

        // Obter veículos associados ao condutor (verificar se tem role driver)
        $vehicles = [];
        $userRoles = explode(',', $user->roles ?? '');
        if (in_array('driver', $userRoles)) {
            $vehicles = \backend\modules\api\models\Vehicle::find()
                ->where(['driver_id' => $user->id])
                ->all();
        }

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->roles,
                'license_number' => $user->license_number ?? null,
                'license_expiry' => $user->license_expiry ?? null,
                'status' => $user->status,
                'company_id' => $user->company_id,
                'photo' => $user->photo ?? null,
            ],
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->nome ?? $company->name,
                'email' => $company->email,
            ] : null,
            'vehicles' => array_map(function($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'license_plate' => $vehicle->license_plate,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'status' => $vehicle->status,
                ];
            }, $vehicles),
            'roles' => $tokenData['roles'] ?? [],
            'permissions' => $tokenData['permissions'] ?? [],
        ]);
    }

    /**
     * Obter usuários por empresa (apenas para admins)
     * 
     * @param integer $company_id Company ID
     * @return ActiveDataProvider
     */
    public function actionByCompany($company_id)
    {
        // Verificar se usuário tem permissão para ver outras empresas
        $tokenData = Yii::$app->params['token_data'] ?? [];
        $userRoles = $tokenData['roles'] ?? [];
        
        if (!in_array('admin', $userRoles) && $this->getCompanyId() != $company_id) {
            throw new ForbiddenHttpException('Sem permissão para ver usuários de outras empresas');
        }

        $company = \backend\modules\api\models\Company::findOne($company_id);
        if (!$company) {
            throw new NotFoundHttpException('Empresa não encontrada');
        }

        $query = User::find()
            ->where(['company_id' => $company_id])
            ->andWhere(['estado' => 'ativo']);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);
    }

    /**
     * Atualizar foto do perfil
     * 
     * @param integer $id
     * @return array
     */
    public function actionUpdatePhoto($id)
    {
        $model = $this->findModel($id);
        
        // Verificar se é o próprio usuário ou tem permissão
        if ($model->id != $this->getUserId() && !$this->hasPermission('manage_users')) {
            throw new ForbiddenHttpException('Sem permissão para alterar foto de outro usuário');
        }

        $bodyParams = Yii::$app->request->bodyParams;
        if (!isset($bodyParams['photo'])) {
            return $this->errorResponse('Campo photo é obrigatório', 400);
        }

        $model->photo = $bodyParams['photo'];
        
        if ($model->save()) {
            return $this->successResponse([
                'id' => $model->id,
                'photo' => $model->photo,
            ], 'Foto atualizada com sucesso');
        }

        return $this->errorResponse('Erro ao atualizar foto', 400, $model->errors);
    }

    /**
     * {@inheritdoc}
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = User::find()
            ->where(['id' => $id])
            ->andWhere(['company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Usuário não encontrado');
        }

        return $model;
    }

    /**
     * Verificar se usuário tem determinada permissão
     * 
     * @param string $permission
     * @return boolean
     */
    private function hasPermission($permission)
    {
        $tokenData = Yii::$app->params['token_data'] ?? [];
        $permissions = $tokenData['permissions'] ?? [];
        
        return in_array($permission, $permissions);
    }
}
