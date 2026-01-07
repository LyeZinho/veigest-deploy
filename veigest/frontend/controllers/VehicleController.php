<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Vehicle;
use frontend\models\Maintenance;
use frontend\models\FuelLog;
use common\models\Document;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * VehicleController - Vehicle Management
 * 
 * Access Control:
 * - Admin: NO ACCESS (frontend blocked)
 * - Manager: FULL ACCESS (view, create, update, assign, delete)
 * - Driver: READ ONLY (view vehicles only)
 * 
 * Requirements:
 * - RF-FO-004: Vehicle Query
 * - RF-BO-005: Vehicle Management
 */
class VehicleController extends Controller
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
                    // RF-FO-004.1: Vehicle list - vehicles.view (manager and driver)
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.view');
                        },
                    ],
                    // RF-FO-004.2, RF-FO-004.3: Details and status - vehicles.view
                    [
                        'allow' => true,
                        'actions' => ['view', 'history', 'documents'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.view');
                        },
                    ],
                    // RF-BO-005.1: Vehicle registration - vehicles.create (manager only)
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.create');
                        },
                    ],
                    // RF-BO-005.2, RF-BO-005.3: Edit and status management - vehicles.update
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.update');
                        },
                    ],
                    // RF-BO-005.5: Assign to drivers - vehicles.assign
                    [
                        'allow' => true,
                        'actions' => ['assign'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.assign');
                        },
                    ],
                    // Delete vehicle - vehicles.delete
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('vehicles.delete');
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
     * RF-FO-004.1: Lista de veículos
     * Condutores vêem apenas os seus veículos atribuídos
     * Gestores vêem todos os veículos da empresa
     */
    public function actionIndex()
    {
        $companyId = Yii::$app->user->identity->company_id;
        $query = Vehicle::find()->where(['company_id' => $companyId]);

        // Se for condutor, filtrar apenas veículos atribuídos
        if (Yii::$app->user->can('condutor') && !Yii::$app->user->can('vehicles.create')) {
            $query->andWhere(['driver_id' => Yii::$app->user->id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['id', 'license_plate', 'brand', 'status', 'created_at'],
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => new Vehicle(),
        ]);
    }

    /**
     * RF-BO-005.1: Registo de veículos
     */
    public function actionCreate()
    {
        $model = new Vehicle();
        $model->company_id = Yii::$app->user->identity->company_id;
        $model->status = Vehicle::STATUS_ATIVO;
        $model->mileage = 0;

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'Veículo criado com sucesso.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error('Vehicle save failed: ' . json_encode($model->errors), 'vehicle');
            }
        }

        $drivers = Vehicle::getAvailableDrivers($model->company_id);

        return $this->render('create', [
            'model' => $model,
            'drivers' => $drivers,
        ]);
    }

    /**
     * RF-FO-004.2, RF-FO-004.3: Detalhes técnicos e estado do veículo
     * @param int $id
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // RF-FO-004.4: Histórico de utilizações (últimas 5 manutenções)
        $maintenancesProvider = new ActiveDataProvider([
            'query' => $model->getMaintenances(),
            'pagination' => ['pageSize' => 5],
        ]);

        // RF-FO-004.5: Documentação associada
        $documentsProvider = new ActiveDataProvider([
            'query' => $model->getDocuments(),
            'pagination' => ['pageSize' => 5],
        ]);

        // Registos de combustível
        $fuelLogsProvider = new ActiveDataProvider([
            'query' => $model->getFuelLogs(),
            'pagination' => ['pageSize' => 5],
        ]);

        // Sumário de custos
        $costSummary = $model->getCostSummary();

        return $this->render('view', [
            'model' => $model,
            'maintenancesProvider' => $maintenancesProvider,
            'documentsProvider' => $documentsProvider,
            'fuelLogsProvider' => $fuelLogsProvider,
            'costSummary' => $costSummary,
        ]);
    }

    /**
     * RF-FO-004.4: Histórico completo de utilizações
     * @param int $id
     */
    public function actionHistory($id)
    {
        $model = $this->findModel($id);

        // Todas as manutenções
        $maintenancesProvider = new ActiveDataProvider([
            'query' => $model->getMaintenances(),
            'pagination' => ['pageSize' => 20],
        ]);

        // Todos os abastecimentos
        $fuelLogsProvider = new ActiveDataProvider([
            'query' => $model->getFuelLogs(),
            'pagination' => ['pageSize' => 20],
        ]);

        // Todas as rotas
        $routesProvider = new ActiveDataProvider([
            'query' => $model->getRoutes(),
            'pagination' => ['pageSize' => 20],
        ]);

        // Tab ativa (default: maintenance)
        $activeTab = Yii::$app->request->get('tab', 'maintenance');

        return $this->render('history', [
            'model' => $model,
            'maintenanceProvider' => $maintenancesProvider,
            'fuelProvider' => $fuelLogsProvider,
            'routesProvider' => $routesProvider,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * RF-FO-004.5: Documentação associada
     * @param int $id
     */
    public function actionDocuments($id)
    {
        $model = $this->findModel($id);

        $documentProvider = new ActiveDataProvider([
            'query' => $model->getDocuments(),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('documents', [
            'model' => $model,
            'documentProvider' => $documentProvider,
        ]);
    }

    /**
     * RF-BO-005.2, RF-BO-005.3: Edição técnica e gestão de estado
     * @param int $id
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Veículo atualizado com sucesso.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $drivers = Vehicle::getAvailableDrivers($model->company_id);

        return $this->render('update', [
            'model' => $model,
            'drivers' => $drivers,
        ]);
    }

    /**
     * RF-BO-005.5: Atribuição a condutores
     * GET: Mostrar formulário de atribuição
     * POST: Processar atribuição
     * @param int $id
     */
    public function actionAssign($id)
    {
        $model = $this->findModel($id);

        // Se POST, processar atribuição
        if (Yii::$app->request->isPost) {
            $driverId = Yii::$app->request->post('driver_id');
            $model->driver_id = $driverId ?: null;
            if ($model->save(false, ['driver_id'])) {
                Yii::$app->session->setFlash('success', 'Condutor atribuído com sucesso.');
            } else {
                Yii::$app->session->setFlash('error', 'Erro ao atribuir condutor.');
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }

        // GET: Mostrar formulário
        $drivers = Vehicle::getAvailableDrivers($model->company_id);

        return $this->render('assign', [
            'model' => $model,
            'drivers' => $drivers,
        ]);
    }

    /**
     * Eliminar veículo
     * @param int $id
     */
    public function actionDelete($id)
    {
        // Log para debug
        Yii::info('DELETE request received for vehicle ID: ' . $id, __METHOD__);
        Yii::info('Request method: ' . Yii::$app->request->method, __METHOD__);
        Yii::info('Is POST: ' . (Yii::$app->request->isPost ? 'yes' : 'no'), __METHOD__);
        
        // Verificar se é POST (já validado pelo VerbFilter, mas vamos garantir)
        if (!Yii::$app->request->isPost) {
            Yii::$app->session->setFlash('error', 'Método não permitido. Use POST para deletar.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        $model = $this->findModel($id);
        
        // Contar relacionamentos
        $maintenanceCount = $model->getMaintenances()->count();
        $documentCount = $model->getDocuments()->count();
        $fuelLogCount = $model->getFuelLogs()->count();
        
        Yii::info("Vehicle ID $id - Maintenances: $maintenanceCount, Documents: $documentCount, FuelLogs: $fuelLogCount", __METHOD__);
        
        // Usar transação para garantir integridade dos dados
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Deletar fuel logs relacionados
            if ($fuelLogCount > 0) {
                $deleted = FuelLog::deleteAll(['vehicle_id' => $model->id]);
                Yii::info("Deleted $deleted fuel logs", __METHOD__);
            }
            
            // Deletar documentos relacionados
            if ($documentCount > 0) {
                $deleted = Document::deleteAll(['vehicle_id' => $model->id]);
                Yii::info("Deleted $deleted documents", __METHOD__);
            }
            
            // Deletar manutenções relacionadas
            if ($maintenanceCount > 0) {
                $deleted = Maintenance::deleteAll(['vehicle_id' => $model->id]);
                Yii::info("Deleted $deleted maintenances", __METHOD__);
            }
            
            // Deletar o veículo
            if ($model->delete()) {
                $transaction->commit();
                Yii::$app->session->setFlash('success', 'Veículo e seus registos relacionados foram removidos com sucesso.');
                Yii::info("Vehicle ID $id deleted successfully", __METHOD__);
            } else {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Erro ao remover veículo.');
                Yii::error("Failed to delete vehicle ID $id: " . json_encode($model->errors), __METHOD__);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Exception deleting vehicle ID $id: " . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Erro ao remover veículo: ' . $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Encontra o model de veículo pelo ID
     * @param int $id
     * @return Vehicle
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $companyId = Yii::$app->user->identity->company_id;
        $model = Vehicle::findOne([
            'id' => $id,
            'company_id' => $companyId,
        ]);

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Veículo não encontrado.');
    }
}
