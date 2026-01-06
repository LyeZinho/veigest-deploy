<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\SupportTicket;

/**
 * TicketForm is the model behind the ticket form.
 * Now saves to database instead of sending email.
 */
class TicketForm extends Model
{
    public $name;
    public $email;
    public $priority;
    public $category;
    public $subject;
    public $body;
    public $verifyCode;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['name', 'email', 'subject', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            // priority and category are required
            [['priority', 'category'], 'required'],
            // priority must be one of the valid values
            ['priority', 'in', 'range' => ['low', 'medium', 'high', 'urgent']],
            // category must be one of the valid values
            ['category', 'in', 'range' => ['technical', 'billing', 'account', 'feature', 'training', 'other']],
            // verifyCode needs to be entered correctly (only for guests)
            ['verifyCode', 'captcha', 'when' => function($model) {
                return Yii::$app->user->isGuest;
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Nome',
            'email' => 'Email',
            'priority' => 'Prioridade',
            'category' => 'Categoria',
            'subject' => 'Assunto',
            'body' => 'Descrição',
            'verifyCode' => 'Código de Verificação',
        ];
    }

    /**
     * Saves the ticket to database.
     *
     * @return SupportTicket|null The saved ticket or null on failure
     */
    public function saveTicket()
    {
        $ticket = new SupportTicket();
        
        // Get company_id from logged user or use default company (1)
        $companyId = 1;
        $userId = null;
        
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $companyId = $user->company_id ?? 1;
            $userId = $user->id;
        }
        
        $ticket->company_id = $companyId;
        $ticket->user_id = $userId;
        $ticket->name = $this->name;
        $ticket->email = $this->email;
        $ticket->subject = $this->subject;
        $ticket->body = $this->body;
        $ticket->priority = $this->priority;
        $ticket->category = $this->category;
        $ticket->status = SupportTicket::STATUS_OPEN;
        
        if ($ticket->save()) {
            // Optionally send notification email to admin
            $this->sendNotificationEmail($ticket);
            return $ticket;
        }
        
        // Copy errors to form
        foreach ($ticket->getErrors() as $attribute => $errors) {
            foreach ($errors as $error) {
                $this->addError($attribute, $error);
            }
        }
        
        return null;
    }

    /**
     * Sends notification email to admin about new ticket.
     *
     * @param SupportTicket $ticket
     * @return bool
     */
    protected function sendNotificationEmail($ticket)
    {
        try {
            return Yii::$app->mailer->compose()
                ->setTo(Yii::$app->params['adminEmail'] ?? 'admin@veigest.com')
                ->setFrom([Yii::$app->params['senderEmail'] ?? 'noreply@veigest.com' => Yii::$app->params['senderName'] ?? 'VeiGest'])
                ->setReplyTo([$this->email => $this->name])
                ->setSubject('[VeiGest] Novo Ticket #' . $ticket->id . ': ' . $this->subject)
                ->setHtmlBody(
                    '<h2>Novo Ticket de Suporte</h2>' .
                    '<p><strong>Ticket #:</strong> ' . $ticket->id . '</p>' .
                    '<p><strong>Nome:</strong> ' . $this->name . '</p>' .
                    '<p><strong>Email:</strong> ' . $this->email . '</p>' .
                    '<p><strong>Prioridade:</strong> ' . $this->getPriorityLabel() . '</p>' .
                    '<p><strong>Categoria:</strong> ' . $this->getCategoryLabel() . '</p>' .
                    '<p><strong>Assunto:</strong> ' . $this->subject . '</p>' .
                    '<p><strong>Descrição:</strong></p><p>' . nl2br($this->body) . '</p>'
                )
                ->send();
        } catch (\Exception $e) {
            Yii::error('Failed to send ticket notification email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get priority label
     * @return string
     */
    public function getPriorityLabel()
    {
        $labels = SupportTicket::getPriorityOptions();
        return $labels[$this->priority] ?? $this->priority;
    }

    /**
     * Get category label
     * @return string
     */
    public function getCategoryLabel()
    {
        $labels = SupportTicket::getCategoryOptions();
        return $labels[$this->category] ?? $this->category;
    }
    
    /**
     * Pre-fill form with logged user data
     */
    public function loadUserData()
    {
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
        }
    }
}