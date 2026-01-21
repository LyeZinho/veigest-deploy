<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "vehicles".
 *
 * @property int $id
 * @property int $company_id
 * @property string $license_plate
 * @property string $brand
 * @property string $model
 * @property int|null $year
 * @property string $fuel_type
 * @property int $mileage
 * @property string $status
 * @property int|null $driver_id
 * @property string|null $photo
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Company $company
 * @property User $driver
 * @property Document[] $documents
 */
class Vehicle extends ActiveRecord
{
    // Constantes de status
    const STATUS_ACTIVE = 'active';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_INACTIVE = 'inactive';

    // Constantes de tipo de combustível
    const FUEL_GASOLINE = 'gasoline';
    const FUEL_DIESEL = 'diesel';
    const FUEL_ELECTRIC = 'electric';
    const FUEL_HYBRID = 'hybrid';
    const FUEL_OTHER = 'other';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vehicles}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'license_plate', 'brand', 'model'], 'required'],
            [['company_id', 'year', 'mileage', 'driver_id'], 'integer'],
            [['license_plate'], 'string', 'max' => 20],
            [['brand', 'model'], 'string', 'max' => 100],
            [['fuel_type'], 'in', 'range' => [
                self::FUEL_GASOLINE, 
                self::FUEL_DIESEL, 
                self::FUEL_ELECTRIC, 
                self::FUEL_HYBRID, 
                self::FUEL_OTHER
            ]],
            [['fuel_type'], 'default', 'value' => self::FUEL_GASOLINE],
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE, 
                self::STATUS_MAINTENANCE, 
                self::STATUS_INACTIVE
            ]],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['photo'], 'string', 'max' => 255],
            [['mileage'], 'default', 'value' => 0],
            [['year'], 'integer', 'min' => 1900, 'max' => 2030],
            [['created_at', 'updated_at'], 'safe'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['driver_id' => 'id']],
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
            'license_plate' => 'Matrícula',
            'brand' => 'Marca',
            'model' => 'Modelo',
            'year' => 'Ano',
            'fuel_type' => 'Tipo de Combustível',
            'mileage' => 'Quilometragem',
            'status' => 'Estado',
            'driver_id' => 'Condutor',
            'photo' => 'Foto',
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
     * Gets query for [[Driver]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }

    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['vehicle_id' => 'id']);
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
            self::STATUS_MAINTENANCE => 'Em Manutenção',
            self::STATUS_INACTIVE => 'Inativo',
        ];
    }

    /**
     * Retorna lista de tipos de combustível
     * 
     * @return array
     */
    public static function getFuelTypeList()
    {
        return [
            self::FUEL_GASOLINE => 'Gasolina',
            self::FUEL_DIESEL => 'Diesel',
            self::FUEL_ELECTRIC => 'Elétrico',
            self::FUEL_HYBRID => 'Híbrido',
            self::FUEL_OTHER => 'Outro',
        ];
    }

    /**
     * Retorna nome completo do veículo
     * 
     * @return string
     */
    public function getFullName()
    {
        return $this->plate . ' - ' . $this->brand . ' ' . $this->model;
    }
}
