<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "alerts".
 *
 * @property int $id
 * @property int $company_id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property string $priority
 * @property string $status
 * @property array|null $details
 * @property string $created_at
 * @property string|null $resolved_at
 *
 * @property Company $company
 */
class Alert extends ActiveRecord
{
    // Constantes de tipos
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_DOCUMENT = 'document';
    const TYPE_FUEL = 'fuel';
    const TYPE_OTHER = 'other';

    // Constantes de prioridade
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    // Constantes de status
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
            [['description'], 'string'],
            [['details'], 'safe'],
            [['created_at', 'resolved_at'], 'safe'],
            [['type'], 'in', 'range' => [self::TYPE_MAINTENANCE, self::TYPE_DOCUMENT, self::TYPE_FUEL, self::TYPE_OTHER]],
            [['priority'], 'in', 'range' => [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]],
            [['priority'], 'default', 'value' => self::PRIORITY_MEDIUM],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_RESOLVED, self::STATUS_IGNORED]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['title'], 'string', 'max' => 200],
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
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Retorna lista de tipos
     *
     * @return array
     */
    public static function getTypesList()
    {
        return [
            self::TYPE_MAINTENANCE => 'Manutenção',
            self::TYPE_DOCUMENT => 'Documento',
            self::TYPE_FUEL => 'Combustível',
            self::TYPE_OTHER => 'Outro',
        ];
    }

    /**
     * Retorna lista de prioridades
     *
     * @return array
     */
    public static function getPriorityList()
    {
        return [
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_MEDIUM => 'Média',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_CRITICAL => 'Crítica',
        ];
    }

    /**
     * Retorna lista de status
     *
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_RESOLVED => 'Resolvido',
            self::STATUS_IGNORED => 'Ignorado',
        ];
    }

    /**
     * Retorna o label do tipo
     *
     * @return string
     */
    public function getTypeLabel()
    {
        $types = self::getTypesList();
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Retorna o label da prioridade
     *
     * @return string
     */
    public function getPriorityLabel()
    {
        $priorities = self::getPriorityList();
        return $priorities[$this->priority] ?? $this->priority;
    }

    /**
     * Retorna a classe CSS do badge de prioridade
     *
     * @return string
     */
    public function getPriorityBadgeClass()
    {
        switch ($this->priority) {
            case self::PRIORITY_LOW:
                return 'badge-secondary';
            case self::PRIORITY_MEDIUM:
                return 'badge-info';
            case self::PRIORITY_HIGH:
                return 'badge-warning';
            case self::PRIORITY_CRITICAL:
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Retorna estatísticas de alertas por empresa
     *
     * @param int $companyId
     * @return array
     */
    public static function getStatsByCompany($companyId)
    {
        $baseQuery = self::find()->where(['company_id' => $companyId]);
        
        return [
            'total' => (int) (clone $baseQuery)->count(),
            'active' => (int) (clone $baseQuery)->andWhere(['status' => self::STATUS_ACTIVE])->count(),
            'resolved' => (int) (clone $baseQuery)->andWhere(['status' => self::STATUS_RESOLVED])->count(),
            'critical' => (int) (clone $baseQuery)->andWhere(['status' => self::STATUS_ACTIVE, 'priority' => self::PRIORITY_CRITICAL])->count(),
            'high' => (int) (clone $baseQuery)->andWhere(['status' => self::STATUS_ACTIVE, 'priority' => self::PRIORITY_HIGH])->count(),
        ];
    }

    /**
     * Retorna alertas por tipo
     *
     * @param int $companyId
     * @return array
     */
    public static function getAlertsByType($companyId)
    {
        return Yii::$app->db->createCommand("
            SELECT 
                type,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count
            FROM {{%alerts}}
            WHERE company_id = :companyId
            GROUP BY type
        ")->bindValue(':companyId', $companyId)->queryAll();
    }

    /**
     * Retorna alertas recentes
     *
     * @param int $companyId
     * @param int $limit
     * @return array
     */
    public static function getRecent($companyId, $limit = 10)
    {
        return self::find()
            ->where(['company_id' => $companyId, 'status' => self::STATUS_ACTIVE])
            ->orderBy(['priority' => SORT_DESC, 'created_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }
}
