<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Maintenance;
use frontend\models\Vehicle;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * MaintenanceController - Maintenance Management
 * 
 * Access Control:
 * - Admin: NO ACCESS (frontend blocked)
 * - Manager: FULL ACCESS (view, create, update, delete, complete)
 * - Driver: NO ACCESS (maintenance not visible to drivers)
 */
class MaintenanceController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
                    // View maintenance - manager only
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('maintenances.view');
                        },
                    ],
                    // Create maintenance - manager only
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('maintenances.create');
                        },
                    ],
                    // Update maintenance - manager only
                    [
                        'actions' => ['update', 'complete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('maintenances.update');
                        },
                    ],
                    // Delete maintenance - manager only
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('maintenances.delete');
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'complete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Maintenance models.
     *
     * @return mixed
     */
    public function actionIndex($status = 'scheduled')
    {
        $query = Maintenance::find()
            ->where([
                'company_id' => Yii::$app->user->identity->company_id,
                'status' => $status,
            ]);

        // Ordering: sempre por data
        if ($status === 'completed') {
            $orderBy = ['date' => SORT_DESC]; // Mais recentes primeiro
        } else { // scheduled ou overdue
            $orderBy = ['date' => SORT_ASC]; // Mais próximas primeiro
            
            // Para overdue, filtrar datas passadas
            if ($status === 'overdue') {
                $query->andWhere(['<', 'date', date('Y-m-d')]);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy($orderBy),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'status' => $status,
        ]);
    }

    /**
     * Displays a single Maintenance model.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Maintenance model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Maintenance();
        $model->company_id = Yii::$app->user->identity->company_id;
        $model->status = 'scheduled'; // Nova manutenção é sempre agendada

        if ($model->load(Yii::$app->request->post())) {
            // Auto-preencher quilometragem do veículo
            if ($model->vehicle_id) {
                $vehicle = Vehicle::findOne($model->vehicle_id);
                if ($vehicle) {
                    $model->km_registro = $vehicle->quilometragem;
                }
            }
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Manutenção criada com sucesso.');
                return $this->redirect(['index']);
            }
        }

        $vehicles = Vehicle::find()
            ->where(['company_id' => Yii::$app->user->identity->company_id])
            ->select(['id', 'model AS modelo', 'license_plate AS matricula'])
            ->asArray()
            ->all();

        return $this->render('create', [
            'model' => $model,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Updates an existing Maintenance model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Manutenção atualizada com sucesso.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $vehicles = Vehicle::find()
            ->where(['company_id' => Yii::$app->user->identity->company_id])
            ->select(['id', 'model AS modelo', 'license_plate AS matricula'])
            ->asArray()
            ->all();

        return $this->render('update', [
            'model' => $model,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Deletes an existing Maintenance model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Manutenção eliminada com sucesso.');
        return $this->redirect(['index']);
    }

    /**
     * Marks a maintenance as completed.
     * Simply changes status to 'completed' - no automatic creation.
     * 
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionComplete($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== 'scheduled') {
            Yii::$app->session->setFlash('warning', 'Esta manutenção já foi concluída ou não está agendada.');
            return $this->redirect(['index']);
        }

        // Simply mark as completed
        $model->status = 'completed';

        if ($model->save(false)) {
            Yii::$app->session->setFlash('success', 'Manutenção concluída com sucesso!');
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('error', 'Erro ao concluir manutenção.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Maintenance model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Maintenance the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Maintenance::findOne($id)) !== null) {
            // Verify user has access to this company's maintenance
            if ($model->company_id != Yii::$app->user->identity->company_id) {
                throw new NotFoundHttpException('Manutenção não encontrada.');
            }
            return $model;
        }

        throw new NotFoundHttpException('Manutenção não encontrada.');
    }
    }
