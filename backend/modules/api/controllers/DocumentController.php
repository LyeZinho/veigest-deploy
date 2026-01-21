<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\Document;
use backend\modules\api\models\File;
use backend\modules\api\models\Vehicle;

/**
 * Document API Controller
 * 
 * Fornece operações CRUD para documentos com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * .
 */
class DocumentController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Document';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * List documents with filters
     * GET /api/documents
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        $query = Document::find()->where(['company_id' => $companyId]);

        // Filter by type
        $type = Yii::$app->request->get('type');
        if ($type) {
            $query->andWhere(['type' => $type]);
        }

        // Filter by status
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        // Filter by vehicle
        $vehicleId = Yii::$app->request->get('vehicle_id');
        if ($vehicleId) {
            $query->andWhere(['vehicle_id' => $vehicleId]);
        }

        // Filter by driver
        $driverId = Yii::$app->request->get('driver_id');
        if ($driverId) {
            $query->andWhere(['driver_id' => $driverId]);
        }

        // Filter by expiring soon (within X days)
        $expiringSoon = Yii::$app->request->get('expiring_soon');
        if ($expiringSoon) {
            $query->andWhere(['<=', 'expiry_date', date('Y-m-d', strtotime("+{$expiringSoon} days"))]);
            $query->andWhere(['>=', 'expiry_date', date('Y-m-d')]);
            $query->andWhere(['status' => 'valid']);
        }

        // Filter expired
        $expired = Yii::$app->request->get('expired');
        if ($expired === 'true' || $expired === '1') {
            $query->andWhere(['<', 'expiry_date', date('Y-m-d')]);
        }

        // Order by
        $sort = Yii::$app->request->get('sort', 'expiry_date');
        if (strpos($sort, '-') === 0) {
            $query->orderBy([substr($sort, 1) => SORT_DESC]);
        } else {
            $query->orderBy([$sort => SORT_ASC]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * View a specific document
     * GET /api/documents/{id}
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }

    /**
     * Create a new document
     * POST /api/documents
     */
    public function actionCreate()
    {
        $model = new Document();
        $model->company_id = $this->getCompanyId();
        $model->load(Yii::$app->request->bodyParams, '');

        // Validate that file belongs to the same company
        if ($model->file_id) {
            $file = File::findOne(['id' => $model->file_id, 'company_id' => $model->company_id]);
            if (!$file) {
                return $this->errorResponse('Arquivo não encontrado ou não pertence à sua empresa', 400);
            }
        }

        // Validate that vehicle belongs to the same company
        if ($model->vehicle_id) {
            $vehicle = Vehicle::findOne(['id' => $model->vehicle_id, 'company_id' => $model->company_id]);
            if (!$vehicle) {
                return $this->errorResponse('Veículo não encontrado ou não pertence à sua empresa', 400);
            }
        }

        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);
            return $this->successResponse($model, 'Documento criado com sucesso');
        }

        return $this->errorResponse('Erro ao criar documento', 400, $model->errors);
    }

    /**
     * Update a document
     * PUT /api/documents/{id}
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->bodyParams, '');

        if ($model->save()) {
            return $this->successResponse($model, 'Documento atualizado com sucesso');
        }

        return $this->errorResponse('Erro ao atualizar documento', 400, $model->errors);
    }

    /**
     * Delete a document
     * DELETE /api/documents/{id}
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            return $this->successResponse(null, 'Documento excluído com sucesso');
        }

        return $this->errorResponse('Erro ao excluir documento', 500);
    }

    /**
     * Get documents by vehicle
     * GET /api/documents/by-vehicle/{vehicle_id}
     */
    public function actionByVehicle($vehicle_id)
    {
        $companyId = $this->getCompanyId();

        // Verify vehicle belongs to company
        $vehicle = Vehicle::findOne(['id' => $vehicle_id, 'company_id' => $companyId]);
        if (!$vehicle) {
            throw new NotFoundHttpException('Veículo não encontrado.');
        }

        $query = Document::find()
            ->where(['company_id' => $companyId, 'vehicle_id' => $vehicle_id])
            ->orderBy(['expiry_date' => SORT_ASC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get documents by driver
     * GET /api/documents/by-driver/{driver_id}
     */
    public function actionByDriver($driver_id)
    {
        $companyId = $this->getCompanyId();

        $query = Document::find()
            ->where(['company_id' => $companyId, 'driver_id' => $driver_id])
            ->orderBy(['expiry_date' => SORT_ASC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get expiring documents
     * GET /api/documents/expiring
     */
    public function actionExpiring()
    {
        $companyId = $this->getCompanyId();
        $days = Yii::$app->request->get('days', 30);

        $query = Document::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['<=', 'expiry_date', date('Y-m-d', strtotime("+{$days} days"))])
            ->andWhere(['>=', 'expiry_date', date('Y-m-d')])
            ->andWhere(['status' => 'valid'])
            ->orderBy(['expiry_date' => SORT_ASC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 50),
            ],
        ]);
    }

    /**
     * Get expired documents
     * GET /api/documents/expired
     */
    public function actionExpired()
    {
        $companyId = $this->getCompanyId();

        $query = Document::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['<', 'expiry_date', date('Y-m-d')])
            ->orderBy(['expiry_date' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 50),
            ],
        ]);
    }

    /**
     * Get document statistics
     * GET /api/documents/stats
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();

        $totalDocuments = Document::find()->where(['company_id' => $companyId])->count();
        $validDocuments = Document::find()->where(['company_id' => $companyId, 'status' => 'valid'])->count();
        $expiredDocuments = Document::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['<', 'expiry_date', date('Y-m-d')])
            ->count();
        $expiringSoon = Document::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['<=', 'expiry_date', date('Y-m-d', strtotime('+30 days'))])
            ->andWhere(['>=', 'expiry_date', date('Y-m-d')])
            ->andWhere(['status' => 'valid'])
            ->count();

        // By type
        $byType = Yii::$app->db->createCommand("
            SELECT type, COUNT(*) as count
            FROM documents
            WHERE company_id = :company_id
            GROUP BY type
        ")->bindValue(':company_id', $companyId)->queryAll();

        // By vehicle
        $byVehicle = Yii::$app->db->createCommand("
            SELECT v.license_plate, COUNT(d.id) as count
            FROM documents d
            INNER JOIN vehicles v ON d.vehicle_id = v.id
            WHERE d.company_id = :company_id AND d.vehicle_id IS NOT NULL
            GROUP BY d.vehicle_id
            ORDER BY count DESC
            LIMIT 10
        ")->bindValue(':company_id', $companyId)->queryAll();

        return $this->successResponse([
            'total_documents' => (int) $totalDocuments,
            'valid_documents' => (int) $validDocuments,
            'expired_documents' => (int) $expiredDocuments,
            'expiring_soon_30_days' => (int) $expiringSoon,
            'by_type' => $byType,
            'by_vehicle' => $byVehicle,
        ]);
    }

    /**
     * Get document type options
     * GET /api/documents/types
     */
    public function actionTypes()
    {
        return $this->successResponse(Document::getTypeOptions());
    }

    /**
     * Find model with company check
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = Document::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Documento não encontrado.');
        }

        return $model;
    }
}
