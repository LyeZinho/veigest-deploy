<?php

namespace backend\controllers;

use Yii; 
use common\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class UserController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lista utilizadores
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Ver utilizador
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Criar utilizador
     */
    public function actionCreate()
    {
        $model = new User(['scenario' => 'adminCreate']);

        $roles = [
            'admin'    => 'Administrador',
            'manager'   => 'Gestor',
            'driver' => 'Condutor',
        ];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->save()) {
                // RBAC: atribuir role
                $auth = Yii::$app->authManager;

                // Remover roles antigas (se existirem)
                $auth->revokeAll($model->id);

                // Atribuir nova role baseada em tempRole
                if (!empty($model->tempRole)) {
                    $roleObj = $auth->getRole($model->tempRole);
                    if ($roleObj) {
                        $auth->assign($roleObj, $model->id);
                        Yii::$app->session->setFlash('success', 'Utilizador criado com sucesso.');
                    } else {
                        Yii::$app->session->setFlash('warning', 'Utilizador criado, mas papel não foi atribuído.');
                    }
                } else {
                    Yii::$app->session->setFlash('warning', 'Utilizador criado sem papel atribuído.');
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    /**
     * Atualizar utilizador
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';
        
        // Pré-preencher tempRole com o papel atual do utilizador
        $model->tempRole = $model->getRole();

        $roles = [
            'admin'    => 'Administrador',
            'manager'   => 'Gestor',
            'driver' => 'Condutor',
        ];

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            // Apenas hash a password se foi fornecida uma nova
            if (empty($model->password)) {
                $model->password = null;
            }
            
            if ($model->validate() && $model->save()) {
                // RBAC: atualizar role
                $auth = Yii::$app->authManager;

                // Remover roles antigas
                $auth->revokeAll($model->id);

                // Atribuir nova role baseada em tempRole
                if (!empty($model->tempRole)) {
                    $roleObj = $auth->getRole($model->tempRole);
                    if ($roleObj) {
                        $auth->assign($roleObj, $model->id);
                        Yii::$app->session->setFlash('success', 'Utilizador atualizado com sucesso.');
                    } else {
                        Yii::$app->session->setFlash('warning', 'Utilizador atualizado, mas papel não foi atribuído.');
                    }
                } else {
                    Yii::$app->session->setFlash('warning', 'Utilizador atualizado sem papel atribuído.');
                }

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'roles' => $roles,
        ]);
    }

    /**
     * Apagar utilizador
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id);

        Yii::$app->authManager->revokeAll($id);

        $user->delete();

        return $this->redirect(['index']);
    }

    /**
     * Encontra modelo User pelo ID
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('O utilizador não existe.');
    }
}
