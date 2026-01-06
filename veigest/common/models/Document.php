<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "documents".
 *
 * @property int $id
 * @property int $company_id
 * @property int $file_id
 * @property int|null $vehicle_id
 * @property int|null $driver_id
 * @property string $type
 * @property string|null $expiry_date
 * @property string $status
 * @property string|null $notes
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Company $company
 * @property File $file
 * @property Vehicle $vehicle
 * @property User $driver
 */
class Document extends ActiveRecord
{
    // Constantes para tipos de documento
    const TYPE_REGISTRATION = 'registration';
    const TYPE_INSURANCE = 'insurance';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_LICENSE = 'license';
    const TYPE_OTHER = 'other';

    // Constantes para status
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
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
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
            [['company_id', 'file_id', 'type'], 'required'],
            [['company_id', 'file_id', 'vehicle_id', 'driver_id'], 'integer'],
            [['type', 'status'], 'string'],
            [['expiry_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            
            // Validações de tipo
            ['type', 'in', 'range' => [
                self::TYPE_REGISTRATION, 
                self::TYPE_INSURANCE, 
                self::TYPE_INSPECTION, 
                self::TYPE_LICENSE, 
                self::TYPE_OTHER
            ]],
            
            // Validações de status
            ['status', 'in', 'range' => [self::STATUS_VALID, self::STATUS_EXPIRED]],
            ['status', 'default', 'value' => self::STATUS_VALID],
            
            // Chaves estrangeiras
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => File::class, 'targetAttribute' => ['file_id' => 'id']],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_id' => 'id']],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['driver_id' => 'id']],
            
            // Pelo menos veículo ou motorista deve ser preenchido
            [['vehicle_id', 'driver_id'], 'validateAssociation'],
        ];
    }

    /**
     * Valida que pelo menos veículo ou motorista está associado (opcional)
     */
    public function validateAssociation($attribute, $params)
    {
        // Esta validação é opcional - documentos podem não ter associação
        // Se quiser tornar obrigatório, descomente:
        // if (empty($this->vehicle_id) && empty($this->driver_id)) {
        //     $this->addError($attribute, 'Pelo menos um veículo ou motorista deve ser associado.');
        // }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'file_id' => 'Ficheiro',
            'vehicle_id' => 'Veículo',
            'driver_id' => 'Motorista',
            'type' => 'Tipo',
            'expiry_date' => 'Data de Validade',
            'status' => 'Estado',
            'notes' => 'Observações',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
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
     * Gets query for [[File]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    /**
     * Gets query for [[Vehicle]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }

    /**
     * Gets query for [[Driver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }

    /**
     * Retorna a lista de tipos de documento
     * 
     * @return array
     */
    public static function getTypesList()
    {
        return [
            self::TYPE_REGISTRATION => 'Registo/DUA',
            self::TYPE_INSURANCE => 'Seguro',
            self::TYPE_INSPECTION => 'Inspeção',
            self::TYPE_LICENSE => 'Licença/Carta',
            self::TYPE_OTHER => 'Outro',
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
     * Retorna a lista de status
     * 
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_VALID => 'Válido',
            self::STATUS_EXPIRED => 'Expirado',
        ];
    }

    /**
     * Retorna o label do status
     * 
     * @return string
     */
    public function getStatusLabel()
    {
        $statuses = self::getStatusList();
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Retorna a classe CSS do badge de status
     * 
     * @return string
     */
    public function getStatusBadgeClass()
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return 'badge-danger';
        }
        
        // Verificar se está próximo do vencimento (30 dias)
        if ($this->isExpiringSoon()) {
            return 'badge-warning';
        }
        
        return 'badge-success';
    }

    /**
     * Retorna o label do status com verificação de vencimento
     * 
     * @return string
     */
    public function getStatusDisplayLabel()
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return 'Expirado';
        }
        
        if ($this->isExpiringSoon()) {
            return 'Próximo do Vencimento';
        }
        
        return 'Válido';
    }

    /**
     * Verifica se o documento está próximo do vencimento
     * 
     * @param int $days Número de dias para considerar "próximo"
     * @return bool
     */
    public function isExpiringSoon($days = 30)
    {
        if (empty($this->expiry_date)) {
            return false;
        }
        
        $expiryDate = strtotime($this->expiry_date);
        $today = strtotime('today');
        $warningDate = strtotime("+{$days} days");
        
        return $expiryDate > $today && $expiryDate <= $warningDate;
    }

    /**
     * Verifica se o documento está expirado
     * 
     * @return bool
     */
    public function isExpired()
    {
        if (empty($this->expiry_date)) {
            return false;
        }
        
        return strtotime($this->expiry_date) < strtotime('today');
    }

    /**
     * Atualiza o status baseado na data de validade
     */
    public function updateStatusByExpiryDate()
    {
        if ($this->isExpired()) {
            $this->status = self::STATUS_EXPIRED;
        } else {
            $this->status = self::STATUS_VALID;
        }
    }

    /**
     * Retorna o nome de exibição da associação (veículo ou motorista)
     * 
     * @return string
     */
    public function getAssociationName()
    {
        if ($this->vehicle) {
            return $this->vehicle->plate ?? 'Veículo #' . $this->vehicle_id;
        }
        
        if ($this->driver) {
            return $this->driver->name ?? 'Motorista #' . $this->driver_id;
        }
        
        return 'Sem associação';
    }

    /**
     * Retorna dias até o vencimento
     * 
     * @return int|null
     */
    public function getDaysUntilExpiry()
    {
        if (empty($this->expiry_date)) {
            return null;
        }
        
        $expiryDate = new \DateTime($this->expiry_date);
        $today = new \DateTime('today');
        $diff = $today->diff($expiryDate);
        
        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * Before save - atualiza status automaticamente
     * 
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        
        // Atualizar status baseado na data de validade
        $this->updateStatusByExpiryDate();
        
        return true;
    }

    /**
     * Elimina o ficheiro associado quando eliminar o documento
     * 
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        
        // Eliminar ficheiro associado
        if ($this->file) {
            $this->file->delete();
        }
        
        return true;
    }

    /**
     * Retorna estatísticas de documentos para uma empresa
     * 
     * @param int $companyId
     * @return array
     */
    public static function getStatsByCompany($companyId)
    {
        $total = self::find()->where(['company_id' => $companyId])->count();
        
        $valid = self::find()
            ->where(['company_id' => $companyId, 'status' => self::STATUS_VALID])
            ->andWhere(['OR', ['expiry_date' => null], ['>', 'expiry_date', new Expression('DATE_ADD(CURDATE(), INTERVAL 30 DAY)')]])
            ->count();
        
        $expiringSoon = self::find()
            ->where(['company_id' => $companyId, 'status' => self::STATUS_VALID])
            ->andWhere(['<=', 'expiry_date', new Expression('DATE_ADD(CURDATE(), INTERVAL 30 DAY)')])
            ->andWhere(['>=', 'expiry_date', new Expression('CURDATE()')])
            ->count();
        
        $expired = self::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['<', 'expiry_date', new Expression('CURDATE()')])
            ->count();
        
        return [
            'total' => (int) $total,
            'valid' => (int) $valid,
            'expiring_soon' => (int) $expiringSoon,
            'expired' => (int) $expired,
        ];
    }
}
