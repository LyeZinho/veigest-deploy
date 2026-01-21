<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use common\models\User;
use common\models\Document;

/**
 * This is the model class for table "vehicles".
 *
 * @property int $id
 * @property int $company_id
 * @property string $license_plate
 * @property string|null $brand
 * @property string|null $model
 * @property int|null $year
 * @property string|null $fuel_type
 * @property int $mileage
 * @property string $status
 * @property int|null $driver_id
 * @property string|null $photo
 * @property string $created_at
 * @property string|null $updated_at
 * 
 * @property User $driver
 * @property Maintenance[] $maintenances
 * @property Document[] $documents
 * @property FuelLog[] $fuelLogs
 */
class Vehicle extends ActiveRecord
{
    // Fuel types
    const FUEL_TYPE_GASOLINA  = 'gasoline';
    const FUEL_TYPE_DIESEL    = 'diesel';
    const FUEL_TYPE_ELETRICO  = 'electric';
    const FUEL_TYPE_HIBRIDO   = 'hybrid';
    const FUEL_TYPE_OUTRO     = 'other';

    // Status constants (English values aligned to DB)
    const STATUS_ATIVO        = 'active';
    const STATUS_MANUTENCAO   = 'maintenance';
    const STATUS_INATIVO      = 'inactive';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vehicles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Campos canónicos (BD)
            [['license_plate', 'brand', 'model', 'status'], 'required'],
            [['company_id', 'year', 'mileage', 'driver_id'], 'integer'],
            [['license_plate'], 'string', 'max' => 20],
            [['brand', 'model'], 'string', 'max' => 100],
            [['photo'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_ATIVO, self::STATUS_MANUTENCAO, self::STATUS_INATIVO]],
            [['fuel_type'], 'in', 'range' => [self::FUEL_TYPE_GASOLINA, self::FUEL_TYPE_DIESEL, self::FUEL_TYPE_ELETRICO, self::FUEL_TYPE_HIBRIDO, self::FUEL_TYPE_OUTRO]],
            [
                ['license_plate', 'company_id'],
                'unique',
                'targetAttribute' => ['license_plate', 'company_id'],
                'message' => 'Esta matrícula já está registada na sua empresa.'
            ],

            // Aliases PT (permitir load/validation)
            [['matricula', 'marca', 'modelo', 'ano', 'tipo_combustivel', 'quilometragem', 'estado', 'condutor_id', 'foto'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'license_plate'    => 'License Plate',
            'brand'            => 'Brand',
            'model'            => 'Model',
            'year'             => 'Year',
            'fuel_type'        => 'Fuel Type',
            'mileage'          => 'Mileage',
            'status'           => 'Status',
            'driver_id'        => 'Driver',
            'photo'            => 'Photo',
            'company_id'       => 'Company',
            'created_at'       => 'Created at',
            'updated_at'       => 'Updated at',
            // Aliases PT
            'matricula'        => 'License Plate',
            'marca'            => 'Brand',
            'modelo'           => 'Model',
            'ano'              => 'Year',
            'tipo_combustivel' => 'Fuel Type',
            'quilometragem'    => 'Mileage',
            'estado'           => 'Status',
            'condutor_id'      => 'Driver',
            'foto'             => 'Photo',
        ];
    }

    /* =========================
     * ENUM HELPERS
     * ========================= */

    public static function optsFuelType()
    {
        return [
            self::FUEL_TYPE_GASOLINA => 'Gasoline',
            self::FUEL_TYPE_DIESEL   => 'Diesel',
            self::FUEL_TYPE_ELETRICO => 'Electric',
            self::FUEL_TYPE_HIBRIDO  => 'Hybrid',
            self::FUEL_TYPE_OUTRO    => 'Other',
        ];
    }

    public static function optsStatus()
    {
        return [
            self::STATUS_ATIVO       => 'Active',
            self::STATUS_MANUTENCAO  => 'In Maintenance',
            self::STATUS_INATIVO     => 'Inactive',
        ];
    }

    public function displayFuelType()
    {
        return self::optsFuelType()[$this->fuel_type] ?? '-';
    }

    public function displayStatus()
    {
        return self::optsStatus()[$this->status] ?? '-';
    }

    /* =========================
     * Aliases getters/setters (compat)
     * ========================= */
    // Aliases PT -> colunas canónicas
    public function getMatricula() { return $this->license_plate; }
    public function setMatricula($value) { $this->license_plate = $value; }

    public function getMarca() { return $this->brand; }
    public function setMarca($value) { $this->brand = $value; }

    public function getModelo() { return $this->model; }
    public function setModelo($value) { $this->model = $value; }

    public function getAno() { return $this->year; }
    public function setAno($value) { $this->year = (int)$value; }

    public function getTipo_combustivel() { return $this->fuel_type; }
    public function setTipo_combustivel($value) { $this->fuel_type = $value; }

    public function getQuilometragem() { return $this->mileage; }
    public function setQuilometragem($value) { $this->mileage = (int)$value; }

    public function getEstado() { return $this->status; }
    public function setEstado($value) { $this->status = $value; }

    public function getCondutor_id() { return $this->driver_id; }
    public function setCondutor_id($value) { $this->driver_id = (int)$value; }

    public function getFoto() { return $this->photo; }
    public function setFoto($value) { $this->photo = $value; }

    /* =========================
     * RELATIONS (RF-FO-004)
     * ========================= */

    /**
     * Condutor atribuído ao veículo
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }

    /**
     * Manutenções do veículo (RF-FO-004.4 - Histórico de utilizações)
     * @return \yii\db\ActiveQuery
     */
    public function getMaintenances()
    {
        return $this->hasMany(Maintenance::class, ['vehicle_id' => 'id'])
            ->orderBy(['date' => SORT_DESC]);
    }

    /**
     * Documentos associados ao veículo (RF-FO-004.5)
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['vehicle_id' => 'id']);
    }

    /**
     * Registos de combustível do veículo
     * @return \yii\db\ActiveQuery
     */
    public function getFuelLogs()
    {
        return $this->hasMany(FuelLog::class, ['vehicle_id' => 'id'])
            ->orderBy(['date' => SORT_DESC]);
    }

    /**
     * Rotas onde este veículo foi utilizado
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(Route::class, ['vehicle_id' => 'id'])
            ->orderBy(['start_time' => SORT_DESC]);
    }

    /**
     * Calcula custos totais do veículo
     * @return array
     */
    public function getCostSummary()
    {
        $maintenanceCost = $this->getMaintenances()->sum('cost') ?: 0;
        $fuelCost = $this->getFuelLogs()->sum('value') ?: 0;
        
        return [
            'maintenance_cost' => (float) $maintenanceCost,
            'fuel_cost' => (float) $fuelCost,
            'total_cost' => (float) ($maintenanceCost + $fuelCost),
        ];
    }

    /**
     * Lista de condutores disponíveis para atribuição
     * @return User[] Array de objetos User (condutores)
     */
    public static function getAvailableDrivers($companyId)
    {
        return User::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['not', ['license_number' => null]])
            ->andWhere(['status' => 'active'])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }
}
