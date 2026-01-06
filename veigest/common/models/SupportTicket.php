<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Support Ticket Model
 * 
 * @property int $id
 * @property int $company_id
 * @property int|null $user_id
 * @property string $name
 * @property string $email
 * @property string $subject
 * @property string $body
 * @property string $priority
 * @property string $category
 * @property string $status
 * @property string|null $admin_response
 * @property int|null $responded_by
 * @property string|null $responded_at
 * @property string $created_at
 * @property string $updated_at
 * 
 * @property Company $company
 * @property User $user
 * @property User $responder
 */
class SupportTicket extends ActiveRecord
{
    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    // Category constants
    const CATEGORY_TECHNICAL = 'technical';
    const CATEGORY_BILLING = 'billing';
    const CATEGORY_ACCOUNT = 'account';
    const CATEGORY_FEATURE = 'feature';
    const CATEGORY_TRAINING = 'training';
    const CATEGORY_OTHER = 'other';
    
    // Status constants
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_WAITING_RESPONSE = 'waiting_response';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tickets}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'name', 'email', 'subject', 'body'], 'required'],
            [['company_id', 'user_id', 'responded_by'], 'integer'],
            [['body', 'admin_response'], 'string'],
            [['name'], 'string', 'max' => 150],
            [['email'], 'string', 'max' => 150],
            [['email'], 'email'],
            [['subject'], 'string', 'max' => 255],
            [['priority'], 'in', 'range' => [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH, self::PRIORITY_URGENT]],
            [['category'], 'in', 'range' => [self::CATEGORY_TECHNICAL, self::CATEGORY_BILLING, self::CATEGORY_ACCOUNT, self::CATEGORY_FEATURE, self::CATEGORY_TRAINING, self::CATEGORY_OTHER]],
            [['status'], 'in', 'range' => [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_WAITING_RESPONSE, self::STATUS_RESOLVED, self::STATUS_CLOSED]],
            [['priority'], 'default', 'value' => self::PRIORITY_MEDIUM],
            [['category'], 'default', 'value' => self::CATEGORY_OTHER],
            [['status'], 'default', 'value' => self::STATUS_OPEN],
            [['responded_at', 'created_at', 'updated_at'], 'safe'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['responded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['responded_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'user_id' => 'Utilizador',
            'name' => 'Nome',
            'email' => 'Email',
            'subject' => 'Assunto',
            'body' => 'Descrição',
            'priority' => 'Prioridade',
            'category' => 'Categoria',
            'status' => 'Estado',
            'admin_response' => 'Resposta',
            'responded_by' => 'Respondido Por',
            'responded_at' => 'Data da Resposta',
            'created_at' => 'Criado Em',
            'updated_at' => 'Atualizado Em',
        ];
    }

    /**
     * Gets query for [[Company]].
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Gets query for [[User]].
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Responder]].
     */
    public function getResponder()
    {
        return $this->hasOne(User::class, ['id' => 'responded_by']);
    }

    /**
     * Get priority options
     */
    public static function getPriorityOptions()
    {
        return [
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel()
    {
        $options = self::getPriorityOptions();
        return $options[$this->priority] ?? $this->priority;
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClass()
    {
        $classes = [
            self::PRIORITY_LOW => 'bg-gray-100 text-gray-800',
            self::PRIORITY_MEDIUM => 'bg-blue-100 text-blue-800',
            self::PRIORITY_HIGH => 'bg-orange-100 text-orange-800',
            self::PRIORITY_URGENT => 'bg-red-100 text-red-800',
        ];
        return $classes[$this->priority] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get category options
     */
    public static function getCategoryOptions()
    {
        return [
            self::CATEGORY_TECHNICAL => 'Problema Técnico',
            self::CATEGORY_BILLING => 'Faturação',
            self::CATEGORY_ACCOUNT => 'Conta/Utilizador',
            self::CATEGORY_FEATURE => 'Solicitação de Funcionalidade',
            self::CATEGORY_TRAINING => 'Formação',
            self::CATEGORY_OTHER => 'Outro',
        ];
    }

    /**
     * Get category label
     */
    public function getCategoryLabel()
    {
        $options = self::getCategoryOptions();
        return $options[$this->category] ?? $this->category;
    }

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_OPEN => 'Aberto',
            self::STATUS_IN_PROGRESS => 'Em Progresso',
            self::STATUS_WAITING_RESPONSE => 'Aguardando Resposta',
            self::STATUS_RESOLVED => 'Resolvido',
            self::STATUS_CLOSED => 'Fechado',
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        $options = self::getStatusOptions();
        return $options[$this->status] ?? $this->status;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        $classes = [
            self::STATUS_OPEN => 'bg-green-100 text-green-800',
            self::STATUS_IN_PROGRESS => 'bg-blue-100 text-blue-800',
            self::STATUS_WAITING_RESPONSE => 'bg-yellow-100 text-yellow-800',
            self::STATUS_RESOLVED => 'bg-purple-100 text-purple-800',
            self::STATUS_CLOSED => 'bg-gray-100 text-gray-800',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Check if ticket can be responded to
     */
    public function canRespond()
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_WAITING_RESPONSE]);
    }

    /**
     * Respond to ticket
     */
    public function respond($response, $responderId)
    {
        $this->admin_response = $response;
        $this->responded_by = $responderId;
        $this->responded_at = date('Y-m-d H:i:s');
        $this->status = self::STATUS_WAITING_RESPONSE;
        return $this->save();
    }

    /**
     * Close ticket
     */
    public function close()
    {
        $this->status = self::STATUS_CLOSED;
        return $this->save();
    }

    /**
     * Resolve ticket
     */
    public function resolve()
    {
        $this->status = self::STATUS_RESOLVED;
        return $this->save();
    }

    /**
     * Get tickets count by status
     */
    public static function getCountByStatus($companyId = null)
    {
        $query = self::find();
        if ($companyId) {
            $query->andWhere(['company_id' => $companyId]);
        }
        
        return [
            'open' => (clone $query)->andWhere(['status' => self::STATUS_OPEN])->count(),
            'in_progress' => (clone $query)->andWhere(['status' => self::STATUS_IN_PROGRESS])->count(),
            'waiting_response' => (clone $query)->andWhere(['status' => self::STATUS_WAITING_RESPONSE])->count(),
            'resolved' => (clone $query)->andWhere(['status' => self::STATUS_RESOLVED])->count(),
            'closed' => (clone $query)->andWhere(['status' => self::STATUS_CLOSED])->count(),
            'total' => (clone $query)->count(),
        ];
    }
}
