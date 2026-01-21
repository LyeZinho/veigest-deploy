<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use backend\modules\api\models\Alert;
use backend\modules\api\components\MqttPublisher;

/**
 * Alert API Controller
 * 
 * Fornece operações CRUD para alertas com multi-tenancy
 * Implementa filtragem automática por company_id
 * Suporta publicação MQTT para atualização dinâmica de clientes
 * 
 * .
 */
class AlertController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\Alert';

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
     * List alerts with filters
     * GET /api/alerts
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        $query = Alert::find()->where(['company_id' => $companyId]);

        // Filter by type
        $type = Yii::$app->request->get('type');
        if ($type) {
            $query->andWhere(['type' => $type]);
        }

        // Filter by status
        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        } else {
            // Default: show only active alerts
            $query->andWhere(['status' => Alert::STATUS_ACTIVE]);
        }

        // Filter by priority
        $priority = Yii::$app->request->get('priority');
        if ($priority) {
            $query->andWhere(['priority' => $priority]);
        }

        // Order by priority (critical first) then by date
        $query->orderBy([
            'FIELD(priority, "critical", "high", "medium", "low")' => SORT_ASC,
            'created_at' => SORT_DESC,
        ]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * View a specific alert
     * GET /api/alerts/{id}
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }

    /**
     * Create a new alert
     * POST /api/alerts
     */
    public function actionCreate()
    {
        $model = new Alert();
        $model->company_id = $this->getCompanyId();
        $model->load(Yii::$app->request->bodyParams, '');

        // Handle JSON details
        $details = Yii::$app->request->getBodyParam('details');
        if ($details && is_array($details)) {
            $model->details = json_encode($details);
        }

        if ($model->save()) {
            Yii::$app->response->setStatusCode(201);
            return $this->successResponse($model, 'Alerta criado com sucesso');
        }

        return $this->errorResponse('Erro ao criar alerta', 400, $model->errors);
    }

    /**
     * Update an alert
     * PUT /api/alerts/{id}
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->bodyParams, '');

        // Handle JSON details
        $details = Yii::$app->request->getBodyParam('details');
        if ($details && is_array($details)) {
            $model->details = json_encode($details);
        }

        if ($model->save()) {
            return $this->successResponse($model, 'Alerta atualizado com sucesso');
        }

        return $this->errorResponse('Erro ao atualizar alerta', 400, $model->errors);
    }

    /**
     * Delete an alert
     * DELETE /api/alerts/{id}
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            return $this->successResponse(null, 'Alerta excluído com sucesso');
        }

        return $this->errorResponse('Erro ao excluir alerta', 500);
    }

    /**
     * Resolve an alert
     * POST /api/alerts/{id}/resolve
     */
    public function actionResolve($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Alert::STATUS_ACTIVE) {
            return $this->errorResponse('Este alerta já foi processado', 400);
        }

        if ($model->resolve()) {
            return $this->successResponse($model, 'Alerta resolvido com sucesso');
        }

        return $this->errorResponse('Erro ao resolver alerta', 500);
    }

    /**
     * Ignore an alert
     * POST /api/alerts/{id}/ignore
     */
    public function actionIgnore($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== Alert::STATUS_ACTIVE) {
            return $this->errorResponse('Este alerta já foi processado', 400);
        }

        if ($model->ignore()) {
            return $this->successResponse($model, 'Alerta ignorado com sucesso');
        }

        return $this->errorResponse('Erro ao ignorar alerta', 500);
    }

    /**
     * Get alerts by type
     * GET /api/alerts/by-type/{type}
     */
    public function actionByType($type)
    {
        $companyId = $this->getCompanyId();

        $query = Alert::find()
            ->where(['company_id' => $companyId, 'type' => $type])
            ->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get alerts by priority
     * GET /api/alerts/by-priority/{priority}
     */
    public function actionByPriority($priority)
    {
        $companyId = $this->getCompanyId();

        $query = Alert::find()
            ->where(['company_id' => $companyId, 'priority' => $priority, 'status' => Alert::STATUS_ACTIVE])
            ->orderBy(['created_at' => SORT_DESC]);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * Get active alerts count
     * GET /api/alerts/count
     */
    public function actionCount()
    {
        $companyId = $this->getCompanyId();

        $totalActive = Alert::find()
            ->where(['company_id' => $companyId, 'status' => Alert::STATUS_ACTIVE])
            ->count();

        $byCritical = Alert::find()
            ->where(['company_id' => $companyId, 'status' => Alert::STATUS_ACTIVE, 'priority' => Alert::PRIORITY_CRITICAL])
            ->count();

        $byHigh = Alert::find()
            ->where(['company_id' => $companyId, 'status' => Alert::STATUS_ACTIVE, 'priority' => Alert::PRIORITY_HIGH])
            ->count();

        return $this->successResponse([
            'total_active' => (int) $totalActive,
            'critical' => (int) $byCritical,
            'high' => (int) $byHigh,
        ]);
    }

    /**
     * Get alert statistics
     * GET /api/alerts/stats
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();

        $totalAlerts = Alert::find()->where(['company_id' => $companyId])->count();
        $activeAlerts = Alert::find()->where(['company_id' => $companyId, 'status' => Alert::STATUS_ACTIVE])->count();
        $resolvedAlerts = Alert::find()->where(['company_id' => $companyId, 'status' => Alert::STATUS_RESOLVED])->count();
        $ignoredAlerts = Alert::find()->where(['company_id' => $companyId, 'status' => Alert::STATUS_IGNORED])->count();

        // By type
        $byType = Yii::$app->db->createCommand("
            SELECT type, status, COUNT(*) as count
            FROM alerts
            WHERE company_id = :company_id
            GROUP BY type, status
        ")->bindValue(':company_id', $companyId)->queryAll();

        // By priority (active only)
        $byPriority = Yii::$app->db->createCommand("
            SELECT priority, COUNT(*) as count
            FROM alerts
            WHERE company_id = :company_id AND status = 'active'
            GROUP BY priority
            ORDER BY FIELD(priority, 'critical', 'high', 'medium', 'low')
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Average resolution time (in hours)
        $avgResolutionTime = Yii::$app->db->createCommand("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours
            FROM alerts
            WHERE company_id = :company_id AND status = 'resolved' AND resolved_at IS NOT NULL
        ")->bindValue(':company_id', $companyId)->queryScalar();

        // Recent alerts (last 7 days)
        $recentAlerts = Alert::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'created_at', date('Y-m-d', strtotime('-7 days'))])
            ->count();

        return $this->successResponse([
            'total_alerts' => (int) $totalAlerts,
            'active_alerts' => (int) $activeAlerts,
            'resolved_alerts' => (int) $resolvedAlerts,
            'ignored_alerts' => (int) $ignoredAlerts,
            'by_type' => $byType,
            'by_priority' => $byPriority,
            'avg_resolution_time_hours' => $avgResolutionTime ? round($avgResolutionTime, 2) : null,
            'recent_alerts_7_days' => (int) $recentAlerts,
        ]);
    }

    /**
     * Get type options
     * GET /api/alerts/types
     */
    public function actionTypes()
    {
        return $this->successResponse(Alert::getTypeOptions());
    }

    /**
     * Get priority options
     * GET /api/alerts/priorities
     */
    public function actionPriorities()
    {
        return $this->successResponse(Alert::getPriorityOptions());
    }

    /**
     * Bulk resolve alerts
     * POST /api/alerts/bulk-resolve
     */
    public function actionBulkResolve()
    {
        $ids = Yii::$app->request->getBodyParam('ids', []);
        
        if (empty($ids)) {
            return $this->errorResponse('Nenhum ID fornecido', 400);
        }

        $companyId = $this->getCompanyId();
        $resolved = 0;

        foreach ($ids as $id) {
            $model = Alert::findOne(['id' => $id, 'company_id' => $companyId, 'status' => Alert::STATUS_ACTIVE]);
            if ($model && $model->resolve()) {
                $resolved++;
            }
        }

        return $this->successResponse([
            'resolved_count' => $resolved,
            'total_requested' => count($ids),
        ], "Resolvidos $resolved de " . count($ids) . " alertas");
    }

    /**
     * Broadcast alert via MQTT
     * POST /api/alerts/{id}/broadcast
     * 
     * Permite enviar manualmente um alerta existente para os clientes via MQTT
     */
    public function actionBroadcast($id)
    {
        $model = $this->findModel($id);
        
        try {
            $mqtt = new MqttPublisher();
            $event = $model->status === Alert::STATUS_ACTIVE 
                ? MqttPublisher::EVENT_NEW 
                : MqttPublisher::EVENT_UPDATED;
            
            $result = $mqtt->publishAlert($model->company_id, $model->toArray(), $event);
            
            if ($result) {
                return $this->successResponse([
                    'alert_id' => $model->id,
                    'event' => $event,
                    'topics' => [
                        MqttPublisher::TOPIC_PREFIX . "/{$model->company_id}",
                        MqttPublisher::TOPIC_PREFIX . "/{$model->company_id}/{$event}",
                    ],
                ], 'Alerta publicado via MQTT com sucesso');
            }
            
            return $this->errorResponse('Falha ao publicar no broker MQTT', 503);
            
        } catch (\Exception $e) {
            Yii::error("MQTT broadcast error: " . $e->getMessage(), 'mqtt');
            return $this->errorResponse('Erro ao conectar ao broker MQTT: ' . $e->getMessage(), 503);
        }
    }

    /**
     * Get MQTT channel information
     * GET /api/alerts/mqtt-info
     * 
     * Retorna informação sobre os canais MQTT disponíveis para subscrição
     */
    public function actionMqttInfo()
    {
        $companyId = $this->getCompanyId();
        $baseTopic = MqttPublisher::TOPIC_PREFIX . "/{$companyId}";
        
        return $this->successResponse([
            'broker' => [
                'host' => 'mosquitto',
                'port' => 1883,
                'protocol' => 'mqtt',
            ],
            'channels' => [
                [
                    'topic' => $baseTopic,
                    'description' => 'Todos os alertas da empresa',
                    'events' => ['new', 'resolved', 'ignored', 'updated'],
                ],
                [
                    'topic' => "{$baseTopic}/new",
                    'description' => 'Novos alertas criados',
                ],
                [
                    'topic' => "{$baseTopic}/resolved",
                    'description' => 'Alertas resolvidos',
                ],
                [
                    'topic' => "{$baseTopic}/ignored",
                    'description' => 'Alertas ignorados',
                ],
                [
                    'topic' => "{$baseTopic}/critical",
                    'description' => 'Alertas de prioridade crítica',
                ],
                [
                    'topic' => "{$baseTopic}/high",
                    'description' => 'Alertas de alta prioridade',
                ],
            ],
            'payload_format' => [
                'event' => 'Tipo de evento (new, resolved, ignored, updated)',
                'timestamp' => 'Data/hora ISO 8601',
                'data' => 'Objeto com dados do alerta',
            ],
            'example_payload' => [
                'event' => 'new',
                'timestamp' => date('c'),
                'data' => [
                    'id' => 1,
                    'type' => 'maintenance',
                    'title' => 'Manutenção Agendada',
                    'priority' => 'high',
                    'status' => 'active',
                ],
            ],
        ]);
    }

    /**
     * Find model with company check
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = Alert::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Alerta não encontrado.');
        }

        return $model;
    }
}
