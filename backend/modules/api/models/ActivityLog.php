<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use common\models\User;

/**
 * ActivityLog API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $user_id
 * @property string $action
 * @property string $entity
 * @property integer $entity_id
 * @property string $details
 * @property string $ip
 * @property string $created_at
 */
class ActivityLog extends ActiveRecord
{
    // Common actions
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_UPLOAD = 'upload';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_EXPORT = 'export';

    // Common entities
    const ENTITY_USER = 'user';
    const ENTITY_VEHICLE = 'vehicle';
    const ENTITY_MAINTENANCE = 'maintenance';
    const ENTITY_DOCUMENT = 'document';
    const ENTITY_FUEL_LOG = 'fuel_log';
    const ENTITY_ALERT = 'alert';
    const ENTITY_FILE = 'file';
    const ENTITY_ROUTE = 'route';
    const ENTITY_COMPANY = 'company';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%activity_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'action', 'entity'], 'required'],
            [['company_id', 'user_id', 'entity_id'], 'integer'],
            [['action'], 'string', 'max' => 255],
            [['entity'], 'string', 'max' => 100],
            [['ip'], 'string', 'max' => 45],
            [['details'], 'safe'], // JSON field
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
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
            'action' => 'Ação',
            'entity' => 'Entidade',
            'entity_id' => 'ID da Entidade',
            'details' => 'Detalhes',
            'ip' => 'Endereço IP',
            'created_at' => 'Criado em',
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
            'user_id',
            'action',
            'action_label' => function ($model) {
                return $this->getActionLabel($model->action);
            },
            'entity',
            'entity_label' => function ($model) {
                return $this->getEntityLabel($model->entity);
            },
            'entity_id',
            'details' => function ($model) {
                return is_string($model->details) ? json_decode($model->details, true) : $model->details;
            },
            'ip',
            'created_at',
            'time_ago' => function ($model) {
                return $this->getTimeAgo();
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
            'user',
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
     * Get user relationship
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Get action label
     */
    public function getActionLabel($action)
    {
        $labels = [
            self::ACTION_CREATE => 'Criação',
            self::ACTION_UPDATE => 'Atualização',
            self::ACTION_DELETE => 'Exclusão',
            self::ACTION_VIEW => 'Visualização',
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_UPLOAD => 'Upload',
            self::ACTION_DOWNLOAD => 'Download',
            self::ACTION_EXPORT => 'Exportação',
        ];
        return $labels[$action] ?? ucfirst($action);
    }

    /**
     * Get entity label
     */
    public function getEntityLabel($entity)
    {
        $labels = [
            self::ENTITY_USER => 'Utilizador',
            self::ENTITY_VEHICLE => 'Veículo',
            self::ENTITY_MAINTENANCE => 'Manutenção',
            self::ENTITY_DOCUMENT => 'Documento',
            self::ENTITY_FUEL_LOG => 'Abastecimento',
            self::ENTITY_ALERT => 'Alerta',
            self::ENTITY_FILE => 'Arquivo',
            self::ENTITY_ROUTE => 'Rota',
            self::ENTITY_COMPANY => 'Empresa',
        ];
        return $labels[$entity] ?? ucfirst($entity);
    }

    /**
     * Get time ago in human readable format
     */
    public function getTimeAgo()
    {
        $created = new \DateTime($this->created_at);
        $now = new \DateTime();
        $diff = $now->diff($created);

        if ($diff->y > 0) {
            return $diff->y . ' ano' . ($diff->y > 1 ? 's' : '') . ' atrás';
        } elseif ($diff->m > 0) {
            return $diff->m . ' mês' . ($diff->m > 1 ? 'es' : '') . ' atrás';
        } elseif ($diff->d > 0) {
            return $diff->d . ' dia' . ($diff->d > 1 ? 's' : '') . ' atrás';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora' . ($diff->h > 1 ? 's' : '') . ' atrás';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minuto' . ($diff->i > 1 ? 's' : '') . ' atrás';
        } else {
            return 'Agora mesmo';
        }
    }

    /**
     * Log an activity
     */
    public static function log($companyId, $action, $entity, $entityId = null, $details = null, $userId = null)
    {
        $log = new self();
        $log->company_id = $companyId;
        $log->user_id = $userId ?? (\Yii::$app->user->isGuest ? null : \Yii::$app->user->id);
        $log->action = $action;
        $log->entity = $entity;
        $log->entity_id = $entityId;
        $log->details = $details ? json_encode($details) : null;
        $log->ip = \Yii::$app->request->userIP ?? null;
        
        return $log->save();
    }

    /**
     * Get all action options
     */
    public static function getActionOptions()
    {
        return [
            self::ACTION_CREATE => 'Criação',
            self::ACTION_UPDATE => 'Atualização',
            self::ACTION_DELETE => 'Exclusão',
            self::ACTION_VIEW => 'Visualização',
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_UPLOAD => 'Upload',
            self::ACTION_DOWNLOAD => 'Download',
            self::ACTION_EXPORT => 'Exportação',
        ];
    }

    /**
     * Get all entity options
     */
    public static function getEntityOptions()
    {
        return [
            self::ENTITY_USER => 'Utilizador',
            self::ENTITY_VEHICLE => 'Veículo',
            self::ENTITY_MAINTENANCE => 'Manutenção',
            self::ENTITY_DOCUMENT => 'Documento',
            self::ENTITY_FUEL_LOG => 'Abastecimento',
            self::ENTITY_ALERT => 'Alerta',
            self::ENTITY_FILE => 'Arquivo',
            self::ENTITY_ROUTE => 'Rota',
            self::ENTITY_COMPANY => 'Empresa',
        ];
    }
}
