<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use frontend\models\Route;
use frontend\models\Vehicle;
use yii\helpers\ArrayHelper;
use yii\db\Query;

/**
 * RouteController - Route Management
 * 
 * Access Control:
 * - Admin: NO ACCESS (frontend blocked)
 * - Manager: FULL ACCESS (view, create, update, delete)
 * - Driver: READ ONLY (view routes only)
 */
class RouteController extends Controller
{
    public $layout = 'dashboard';

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
                    // View routes - manager and driver
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('routes.view');
                        },
                    ],
                    // Create routes - manager only
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('routes.create');
                        },
                    ],
                    // Update routes - manager only
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('routes.update');
                        },
                    ],
                    // Delete routes - manager only
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('routes.delete');
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;

        $query = Route::find()->where(['company_id' => $companyId])->orderBy(['start_time' => SORT_DESC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
            'sort' => [
                'defaultOrder' => ['start_time' => SORT_DESC],
                'attributes' => ['id', 'start_time'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->ensureCompanyAccess($model->company_id);
        return $this->render('view', ['model' => $model]);
    }

    public function actionCreate()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $model = new Route();
        $model->company_id = $companyId;
        $model->start_time = date('Y-m-d H:i');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'drivers' => $this->getCompanyDriversViaRBAC($companyId),
            'vehicles' => $this->getCompanyVehicles($companyId),
        ]);
    }

    public function actionUpdate($id)
    {
        $companyId = Yii::$app->user->identity->company_id;
        $model = $this->findModel($id);
        $this->ensureCompanyAccess($model->company_id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'drivers' => $this->getCompanyDriversViaRBAC($companyId),
            'vehicles' => $this->getCompanyVehicles($companyId),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->ensureCompanyAccess($model->company_id);
        $model->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id): Route
    {
        $model = Route::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Rota não encontrada.');
        }
        return $model;
    }

    protected function ensureCompanyAccess($companyId)
    {
        if ((int)$companyId !== (int)Yii::$app->user->identity->company_id) {
            throw new ForbiddenHttpException('Sem acesso a esta empresa.');
        }
    }

    // Filter drivers via RBAC (auth_assignment), accept either 'driver' or 'condutor'
    protected function getCompanyDriversViaRBAC($companyId): array
    {
        $rows = (new Query())
            ->select(['u.id', 'u.name'])
            ->from(['u' => 'users'])
            ->innerJoin(['aa' => 'auth_assignment'], 'aa.user_id = u.id')
            ->where(['u.company_id' => $companyId])
            ->andWhere(['in', 'aa.item_name', ['driver', 'condutor']])
            ->all();
        return ArrayHelper::map($rows, 'id', 'name');
    }

    protected function getCompanyVehicles($companyId): array
    {
        $rows = (new Query())
            ->select(['id', 'license_plate AS matricula'])
            ->from('vehicles')
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();
        return ArrayHelper::map($rows, 'id', 'matricula');
    }
}
