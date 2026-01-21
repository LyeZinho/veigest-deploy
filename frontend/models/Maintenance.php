<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%maintenances}}".
 *
 * @property int $id
 * @property int $company_id Company ID
 * @property int $vehicle_id Vehicle ID
 * @property string $tipo Type of maintenance
 * @property string|null $descricao Description
 * @property string $data Maintenance date (única data)
 * @property string $status Status (scheduled|completed|overdue)
 * @property float|null $custo Cost
 * @property int|null $km_registro Mileage record
 * @property string|null $oficina Workshop name
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Vehicle $vehicle
 */
class Maintenance extends ActiveRecord
{
    // Constantes de status
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%maintenances}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Campos canónicos (BD)
            [['company_id', 'vehicle_id', 'type', 'date'], 'required'],
            [['company_id', 'vehicle_id', 'mileage_record'], 'integer'],
            [['description'], 'string'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['cost'], 'number'],
            [['type', 'workshop'], 'string', 'max' => 200],
            [['status'], 'in', 'range' => [self::STATUS_SCHEDULED, self::STATUS_COMPLETED, self::STATUS_CANCELLED]],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_id' => 'id']],

            // Aliases PT (permitir load/validation)
            [['tipo', 'descricao', 'data', 'custo', 'km_registro', 'oficina'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
            [['company_id', 'vehicle_id', 'tipo'], 'required'],
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company',
            'vehicle_id' => 'Vehicle',
            'type' => 'Maintenance Type',
            'description' => 'Description',
            'date' => 'Date',
            'status' => 'Status',
            'cost' => 'Cost (€)',
            'mileage_record' => 'Mileage',
            'workshop' => 'Workshop',
            // Aliases PT
            'tipo' => 'Maintenance Type',
            'descricao' => 'Description',
            'data' => 'Date',
            'custo' => 'Cost (€)',
            'km_registro' => 'Mileage',
            'oficina' => 'Workshop',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ];
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
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * Get maintenance types available
     */
    public static function getTypes()
    {
        return [
            'Manutenção Preventiva' => 'Manutenção Preventiva',
            'Manutenção Corretiva' => 'Manutenção Corretiva',
            'Inspeção' => 'Inspeção',
            'Reparação' => 'Reparação',
            'Óleo' => 'Óleo',
            'Pneus' => 'Pneus',
            'Freios' => 'Freios',
            'Bateria' => 'Bateria',
            'Filtros' => 'Filtros',
            'Alinhamento' => 'Alinhamento',
            'Outro' => 'Outro',
        ];
    }

    /* =========================
     * Aliases PT -> colunas canónicas
     * ========================= */
    public function getTipo() { return $this->type; }
    public function setTipo($value) { $this->type = $value; }

    public function getDescricao() { return $this->description; }
    public function setDescricao($value) { $this->description = $value; }

    public function getData() { return $this->date; }
    public function setData($value) { $this->date = $value; }

    public function getCusto() { return $this->cost; }
    public function setCusto($value) { $this->cost = $value; }

    public function getKm_registro() { return $this->mileage_record; }
    public function setKm_registro($value) { $this->mileage_record = $value; }

    public function getOficina() { return $this->workshop; }
    public function setOficina($value) { $this->workshop = $value; }
}
