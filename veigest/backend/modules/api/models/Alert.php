<?php

namespace backend\modules\api\models;

use Yii;
use yii\db\ActiveRecord;
use backend\modules\api\components\MqttPublisher;

/**
 * Alert API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $type
 * @property string $title
 * @property string $description
 * @property string $priority
 * @property string $status
 * @property string $details
 * @property string $created_at
 * @property string $resolved_at
 */
class Alert extends ActiveRecord
{
    // Alert types
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_DOCUMENT = 'document';
    const TYPE_FUEL = 'fuel';
    const TYPE_OTHER = 'other';

    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // Status
    const STATUS_ACTIVE = 'active';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_IGNORED = 'ignored';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%alerts}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'type', 'title'], 'required'],
            [['company_id'], 'integer'],
            [['type'], 'in', 'range' => [self::TYPE_MAINTENANCE, self::TYPE_DOCUMENT, self::TYPE_FUEL, self::TYPE_OTHER]],
            [['priority'], 'in', 'range' => [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]],
            [['priority'], 'default', 'value' => self::PRIORITY_MEDIUM],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_RESOLVED, self::STATUS_IGNORED]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['title'], 'string', 'max' => 200],
            [['description'], 'string'],
            [['details'], 'safe'], // JSON field
            [['resolved_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
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
            'type' => 'Tipo',
            'title' => 'Título',
            'description' => 'Descrição',
            'priority' => 'Prioridade',
            'status' => 'Estado',
            'details' => 'Detalhes',
            'created_at' => 'Criado em',
            'resolved_at' => 'Resolvido em',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'company_id',
            'type',
            'type_label' => function ($model) {
                return $this->getTypeLabel($model->type);
            },
            'title',
            'description',
            'priority',
            'priority_label' => function ($model) {
                return $this->getPriorityLabel($model->priority);
            },
            'priority_level' => function ($model) {
                return $this->getPriorityLevel($model->priority);
            },
            'status',
            'status_label' => function ($model) {
                return $this->getStatusLabel($model->status);
            },
            'details' => function ($model) {
                return is_string($model->details) ? json_decode($model->details, true) : $model->details;
            },
            'created_at',
            'resolved_at',
            'age' => function ($model) {
                return $this->getAge();
            },
        ];
    }

    /**
     * Extra fields
     */
    public function extraFields()
    {
        return [
            'company',
        ];
    }

    /**
     * Get company relationship
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Get type label
     */
    public function getTypeLabel($type)
    {
        $labels = [
            self::TYPE_MAINTENANCE => 'Manutenção',
            self::TYPE_DOCUMENT => 'Documento',
            self::TYPE_FUEL => 'Combustível',
            self::TYPE_OTHER => 'Outro',
        ];
        return $labels[$type] ?? $type;
    }

    /**
     * Get priority label
     */
    public function getPriorityLabel($priority)
    {
        $labels = [
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
        return $labels[$priority] ?? $priority;
    }

    /**
     * Get priority level (numeric for sorting)
     */
    public function getPriorityLevel($priority)
    {
        $levels = [
            self::PRIORITY_LOW => 1,
            self::PRIORITY_MEDIUM => 2,
            self::PRIORITY_HIGH => 3,
            self::PRIORITY_CRITICAL => 4,
        ];
        return $levels[$priority] ?? 0;
    }

    /**
     * Get status label
     */
    public function getStatusLabel($status)
    {
        $labels = [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_RESOLVED => 'Resolvido',
            self::STATUS_IGNORED => 'Ignorado',
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * Get age of alert in human readable format
     */
    public function getAge()
    {
        $created = new \DateTime($this->created_at);
        $now = new \DateTime();
        $diff = $now->diff($created);

        if ($diff->days > 30) {
            return $diff->m . ' meses';
        } elseif ($diff->days > 0) {
            return $diff->days . ' dias';
        } elseif ($diff->h > 0) {
            return $diff->h . ' horas';
        } else {
            return $diff->i . ' minutos';
        }
    }

    /**
     * Resolve alert
     */
    public function resolve()
    {
        $this->status = self::STATUS_RESOLVED;
        $this->resolved_at = date('Y-m-d H:i:s');
        $result = $this->save(false);
        
        // Publicar no MQTT
        if ($result) {
            $this->publishToMqtt(MqttPublisher::EVENT_RESOLVED);
        }
        
        return $result;
    }

    /**
     * Ignore alert
     */
    public function ignore()
    {
        $this->status = self::STATUS_IGNORED;
        $result = $this->save(false);
        
        // Publicar no MQTT
        if ($result) {
            $this->publishToMqtt(MqttPublisher::EVENT_IGNORED);
        }
        
        return $result;
    }

    /**
     * After save - publicar novo alerta no MQTT
     * 
     * @param bool $insert Se é inserção
     * @param array $changedAttributes Atributos alterados
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        
        // Publicar apenas novos alertas (insert)
        if ($insert) {
            $this->publishToMqtt(MqttPublisher::EVENT_NEW);
        }
    }

    /**
     * Publicar alerta no broker MQTT
     * 
     * @param string $event Tipo de evento
     */
    protected function publishToMqtt($event)
    {
        try {
            $mqtt = new MqttPublisher();
            $mqtt->publishAlert($this->company_id, $this->toArray(), $event);
        } catch (\Exception $e) {
            // Log error but don't break the application
            Yii::error("MQTT publish error: " . $e->getMessage(), 'mqtt');
        }
    }

    /**
     * Get all type options
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_MAINTENANCE => 'Manutenção',
            self::TYPE_DOCUMENT => 'Documento',
            self::TYPE_FUEL => 'Combustível',
            self::TYPE_OTHER => 'Outro',
        ];
    }

    /**
     * Get all priority options
     */
    public static function getPriorityOptions()
    {
        return [
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
    }

    /**
     * Get all status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_RESOLVED => 'Resolvido',
            self::STATUS_IGNORED => 'Ignorado',
        ];
    }
}
