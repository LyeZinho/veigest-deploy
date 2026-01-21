<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\ActivityLog;

/**
 * ActivityLog API Controller
 * 
 * Fornece operações para logs de atividade com multi-tenancy
 * Apenas leitura - logs são criados automaticamente
 * 
 * .
 */
class ActivityLogController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\ActivityLog';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        // Only allow reading - logs should be created programmatically
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * List activity logs with filters
     * GET /api/activity-logs
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        $query = ActivityLog::find()->where(['company_id' => $companyId]);

        // Filter by user
        $userId = Yii::$app->request->get('user_id');
        if ($userId) {
            $query->andWhere(['user_id' => $userId]);
        }

        // Filter by action
        $action = Yii::$app->request->get('action');
        if ($action) {
            $query->andWhere(['action' => $action]);
        }

        // Filter by entity
        $entity = Yii::$app->request->get('entity');
        if ($entity) {
            $query->andWhere(['entity' => $entity]);
        }

        // Filter by entity_id
        $entityId = Yii::$app->request->get('entity_id');
        if ($entityId) {
            $query->andWhere(['entity_id' => $entityId]);
        }

        // Filter by date range
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        if ($startDate) {
            $query->andWhere(['>=', 'created_at', $startDate . ' 00:00:00']);
        }
        if ($endDate) {
            $query->andWhere(['<=', 'created_at', $endDate . ' 23:59:59']);
        }

        // Search in action or details
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['OR',
                ['LIKE', 'action', $search],
                ['LIKE', 'details', $search],
            ]);
        }

        // Order by date (newest first)
        $query->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 50),
            ],
        ]);
    }

    /**
     * View a specific activity log
     * GET /api/activity-logs/{id}
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }

    /**
     * Get logs by user
     * GET /api/activity-logs/by-user/{user_id}
     */
    public function actionByUser($user_id)
    {
        $companyId = $this->getCompanyId();

        $query = ActivityLog::find()
            ->where(['company_id' => $companyId, 'user_id' => $user_id])
            ->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 50),
            ],
        ]);
    }

    /**
     * Get logs by entity
     * GET /api/activity-logs/by-entity/{entity}/{entity_id}
     */
    public function actionByEntity($entity, $entity_id)
    {
        $companyId = $this->getCompanyId();

        $query = ActivityLog::find()
            ->where([
                'company_id' => $companyId,
                'entity' => $entity,
                'entity_id' => $entity_id,
            ])
            ->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 50),
            ],
        ]);
    }

    /**
     * Get recent activity
     * GET /api/activity-logs/recent
     */
    public function actionRecent()
    {
        $companyId = $this->getCompanyId();
        $limit = Yii::$app->request->get('limit', 20);

        $logs = ActivityLog::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit($limit)
            ->all();

        return $this->successResponse($logs);
    }

    /**
     * Get activity statistics
     * GET /api/activity-logs/stats
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();

        $totalLogs = ActivityLog::find()->where(['company_id' => $companyId])->count();

        // By action
        $byAction = Yii::$app->db->createCommand("
            SELECT action, COUNT(*) as count
            FROM activity_logs
            WHERE company_id = :company_id
            GROUP BY action
            ORDER BY count DESC
        ")->bindValue(':company_id', $companyId)->queryAll();

        // By entity
        $byEntity = Yii::$app->db->createCommand("
            SELECT entity, COUNT(*) as count
            FROM activity_logs
            WHERE company_id = :company_id
            GROUP BY entity
            ORDER BY count DESC
        ")->bindValue(':company_id', $companyId)->queryAll();

        // By user (top 10)
        $byUser = Yii::$app->db->createCommand("
            SELECT u.name as user_name, u.id as user_id, COUNT(al.id) as count
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.company_id = :company_id
            GROUP BY al.user_id
            ORDER BY count DESC
            LIMIT 10
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Activity by day (last 30 days)
        $byDay = Yii::$app->db->createCommand("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM activity_logs
            WHERE company_id = :company_id
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Activity by hour (distribution)
        $byHour = Yii::$app->db->createCommand("
            SELECT HOUR(created_at) as hour, COUNT(*) as count
            FROM activity_logs
            WHERE company_id = :company_id
            GROUP BY HOUR(created_at)
            ORDER BY hour ASC
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Last 24 hours activity
        $last24h = ActivityLog::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'created_at', date('Y-m-d H:i:s', strtotime('-24 hours'))])
            ->count();

        // Last 7 days activity
        $last7days = ActivityLog::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'created_at', date('Y-m-d', strtotime('-7 days'))])
            ->count();

        return $this->successResponse([
            'total_logs' => (int) $totalLogs,
            'last_24h' => (int) $last24h,
            'last_7_days' => (int) $last7days,
            'by_action' => $byAction,
            'by_entity' => $byEntity,
            'by_user' => $byUser,
            'by_day_last_30' => $byDay,
            'by_hour' => $byHour,
        ]);
    }

    /**
     * Get action options
     * GET /api/activity-logs/actions
     */
    public function actionActions()
    {
        return $this->successResponse(ActivityLog::getActionOptions());
    }

    /**
     * Get entity options
     * GET /api/activity-logs/entities
     */
    public function actionEntities()
    {
        return $this->successResponse(ActivityLog::getEntityOptions());
    }

    /**
     * Manually log an activity (for external systems)
     * POST /api/activity-logs
     */
    public function actionCreate()
    {
        $companyId = $this->getCompanyId();
        $userId = $this->getUserId();

        $action = Yii::$app->request->getBodyParam('action');
        $entity = Yii::$app->request->getBodyParam('entity');
        $entityId = Yii::$app->request->getBodyParam('entity_id');
        $details = Yii::$app->request->getBodyParam('details');

        if (!$action || !$entity) {
            return $this->errorResponse('Os campos action e entity são obrigatórios', 400);
        }

        if (ActivityLog::log($companyId, $action, $entity, $entityId, $details, $userId)) {
            Yii::$app->response->setStatusCode(201);
            return $this->successResponse(null, 'Log de atividade criado com sucesso');
        }

        return $this->errorResponse('Erro ao criar log de atividade', 500);
    }

    /**
     * Find model with company check
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = ActivityLog::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Log de atividade não encontrado.');
        }

        return $model;
    }
}
