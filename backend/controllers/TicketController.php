<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use common\models\SupportTicket;

/**
 * TicketController - Backend Support Tickets Management
 * 
 * Access Control:
 * - Admin: FULL ACCESS (view, respond, update status, delete)
 * - Manager: VIEW and RESPOND only (tickets.view, tickets.respond)
 * - Driver: NO ACCESS (403 Forbidden)
 */
class TicketController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    // View tickets: admin and manager
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => ['admin', 'manager'],
                    ],
                    // Respond to tickets: admin and manager
                    [
                        'actions' => ['respond', 'update-status'],
                        'allow' => true,
                        'roles' => ['admin', 'manager'],
                    ],
                    // Delete tickets: admin only
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException(
                        'Não tem permissão para aceder a esta área. Apenas administradores e gestores podem ver tickets de suporte.'
                    );
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'respond' => ['POST'],
                    'update-status' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all support tickets.
     *
     * @return string
     */
    public function actionIndex()
    {
        $status = Yii::$app->request->get('status');
        $priority = Yii::$app->request->get('priority');
        $category = Yii::$app->request->get('category');
        
        $query = SupportTicket::find()->orderBy(['created_at' => SORT_DESC]);
        
        // Filter by status
        if ($status && $status !== 'all') {
            $query->andWhere(['status' => $status]);
        }
        
        // Filter by priority
        if ($priority && $priority !== 'all') {
            $query->andWhere(['priority' => $priority]);
        }
        
        // Filter by category
        if ($category && $category !== 'all') {
            $query->andWhere(['category' => $category]);
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        // Get statistics
        $stats = SupportTicket::getCountByStatus();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'stats' => $stats,
            'currentStatus' => $status,
            'currentPriority' => $priority,
            'currentCategory' => $category,
        ]);
    }

    /**
     * Displays a single ticket.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the ticket cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Responds to a ticket.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRespond($id)
    {
        $model = $this->findModel($id);
        $response = Yii::$app->request->post('response');
        
        if (!$model->canRespond()) {
            Yii::$app->session->setFlash('error', 'Este ticket não pode receber respostas no estado atual.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        if (empty($response)) {
            Yii::$app->session->setFlash('error', 'A resposta não pode estar vazia.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        if ($model->respond($response, Yii::$app->user->id)) {
            Yii::$app->session->setFlash('success', 'Resposta enviada com sucesso!');
            
            // Send email notification to user
            $this->sendResponseNotification($model);
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao enviar a resposta. Por favor, tente novamente.');
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Updates ticket status.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateStatus($id)
    {
        $model = $this->findModel($id);
        $newStatus = Yii::$app->request->post('status');
        
        if (!in_array($newStatus, [
            SupportTicket::STATUS_OPEN,
            SupportTicket::STATUS_IN_PROGRESS,
            SupportTicket::STATUS_WAITING_RESPONSE,
            SupportTicket::STATUS_RESOLVED,
            SupportTicket::STATUS_CLOSED,
        ])) {
            Yii::$app->session->setFlash('error', 'Estado inválido.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        $model->status = $newStatus;
        
        if ($model->save()) {
            Yii::$app->session->setFlash('success', 'Estado do ticket atualizado com sucesso!');
        } else {
            Yii::$app->session->setFlash('error', 'Erro ao atualizar o estado do ticket.');
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Deletes a ticket.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Ticket eliminado com sucesso!');

        return $this->redirect(['index']);
    }

    /**
     * Finds the SupportTicket model based on its primary key value.
     *
     * @param int $id
     * @return SupportTicket the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupportTicket::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('O ticket solicitado não existe.');
    }

    /**
     * Sends email notification to user about response.
     *
     * @param SupportTicket $ticket
     * @return bool
     */
    protected function sendResponseNotification($ticket)
    {
        try {
            return Yii::$app->mailer->compose()
                ->setTo($ticket->email)
                ->setFrom([Yii::$app->params['senderEmail'] ?? 'noreply@veigest.com' => Yii::$app->params['senderName'] ?? 'VeiGest'])
                ->setSubject('[VeiGest] Resposta ao Ticket #' . $ticket->id . ': ' . $ticket->subject)
                ->setHtmlBody(
                    '<h2>Resposta ao seu Ticket de Suporte</h2>' .
                    '<p>Olá ' . $ticket->name . ',</p>' .
                    '<p>O seu ticket #' . $ticket->id . ' recebeu uma resposta:</p>' .
                    '<div style="background-color: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;">' .
                    '<p><strong>Assunto:</strong> ' . $ticket->subject . '</p>' .
                    '<p><strong>Resposta:</strong></p>' .
                    '<p>' . nl2br($ticket->admin_response) . '</p>' .
                    '</div>' .
                    '<p>Se precisar de mais assistência, responda a este email ou crie um novo ticket.</p>' .
                    '<p>Atenciosamente,<br>Equipa VeiGest</p>'
                )
                ->send();
        } catch (\Exception $e) {
            Yii::error('Failed to send ticket response notification: ' . $e->getMessage());
            return false;
        }
    }
}
