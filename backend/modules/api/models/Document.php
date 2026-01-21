<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use common\models\User;

/**
 * Document API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $file_id
 * @property integer $vehicle_id
 * @property integer $driver_id
 * @property string $type
 * @property string $expiry_date
 * @property string $status
 * @property string $notes
 * @property string $created_at
 * @property string $updated_at
 */
class Document extends ActiveRecord
{
    // Document types
    const TYPE_REGISTRATION = 'registration';
    const TYPE_INSURANCE = 'insurance';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_LICENSE = 'license';
    const TYPE_OTHER = 'other';

    // Status
    const STATUS_VALID = 'valid';
    const STATUS_EXPIRED = 'expired';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%documents}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'file_id', 'type'], 'required'],
            [['company_id', 'file_id', 'vehicle_id', 'driver_id'], 'integer'],
            [['type'], 'in', 'range' => [self::TYPE_REGISTRATION, self::TYPE_INSURANCE, self::TYPE_INSPECTION, self::TYPE_LICENSE, self::TYPE_OTHER]],
            [['status'], 'in', 'range' => [self::STATUS_VALID, self::STATUS_EXPIRED]],
            [['status'], 'default', 'value' => self::STATUS_VALID],
            [['expiry_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_id' => 'id']],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['driver_id' => 'id']],
            // Ensure at least vehicle_id or driver_id is provided
            [['vehicle_id', 'driver_id'], 'validateEntityAssociation'],
        ];
    }

    /**
     * Validate that at least one entity is associated
     */
    public function validateEntityAssociation($attribute, $params)
    {
        if (empty($this->vehicle_id) && empty($this->driver_id)) {
            $this->addError($attribute, 'Deve associar o documento a um veículo ou condutor.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'file_id' => 'Arquivo',
            'vehicle_id' => 'Veículo',
            'driver_id' => 'Condutor',
            'type' => 'Tipo',
            'expiry_date' => 'Data de Validade',
            'status' => 'Estado',
            'notes' => 'Notas',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
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
            'file_id',
            'vehicle_id',
            'driver_id',
            'type',
            'type_label' => function ($model) {
                return $this->getTypeLabel($model->type);
            },
            'expiry_date',
            'status',
            'status_label' => function ($model) {
                return $model->status === self::STATUS_VALID ? 'Válido' : 'Expirado';
            },
            'days_to_expiry' => function ($model) {
                return $this->getDaysToExpiry();
            },
            'is_expiring_soon' => function ($model) {
                $days = $this->getDaysToExpiry();
                return $days !== null && $days <= 30 && $days > 0;
            },
            'is_expired' => function ($model) {
                $days = $this->getDaysToExpiry();
                return $days !== null && $days <= 0;
            },
            'notes',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Extra fields
     */
    public function extraFields()
    {
        return [
            'company',
            'file',
            'vehicle',
            'driver',
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
     * Get file relationship
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    /**
     * Get vehicle relationship
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }

    /**
     * Get driver relationship
     */
    public function getDriver()
    {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }

    /**
     * Get type label
     */
    public function getTypeLabel($type)
    {
        $labels = [
            self::TYPE_REGISTRATION => 'Registro',
            self::TYPE_INSURANCE => 'Seguro',
            self::TYPE_INSPECTION => 'Inspeção',
            self::TYPE_LICENSE => 'Licença',
            self::TYPE_OTHER => 'Outro',
        ];
        return $labels[$type] ?? $type;
    }

    /**
     * Get days to expiry
     */
    public function getDaysToExpiry()
    {
        if (!$this->expiry_date) {
            return null;
        }
        $now = new \DateTime();
        $expiry = new \DateTime($this->expiry_date);
        return (int) $now->diff($expiry)->format('%r%a');
    }

    /**
     * Get all type options
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_REGISTRATION => 'Registro',
            self::TYPE_INSURANCE => 'Seguro',
            self::TYPE_INSPECTION => 'Inspeção',
            self::TYPE_LICENSE => 'Licença',
            self::TYPE_OTHER => 'Outro',
        ];
    }

    /**
     * Before save - auto update status based on expiry
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->expiry_date) {
                $days = $this->getDaysToExpiry();
                $this->status = ($days !== null && $days <= 0) ? self::STATUS_EXPIRED : self::STATUS_VALID;
            }
            return true;
        }
        return false;
    }
}
