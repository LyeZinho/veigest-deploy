<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Maintenance API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $vehicle_id
 * @property string $type
 * @property string $description
 * @property double $cost
 * @property string $date
 * @property integer $mileage_record
 * @property string $next_date
 * @property string $workshop
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class Maintenance extends ActiveRecord
{
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
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'type', 'description'], 'required'],
            [['vehicle_id', 'company_id', 'mileage_record'], 'integer'],
            [['cost'], 'number', 'min' => 0],
            [['date', 'next_date'], 'date', 'format' => 'php:Y-m-d'],
            [['type'], 'in', 'range' => ['preventive', 'corrective', 'revision', 'inspection']],
            [['description'], 'string'],
            [['workshop'], 'string', 'max' => 200],
            [['status'], 'in', 'range' => ['scheduled', 'completed', 'cancelled']],
            [['status'], 'default', 'value' => 'scheduled'],
            [['cost'], 'default', 'value' => 0],
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
            'vehicle_id' => 'Veículo',
            'type' => 'Tipo',
            'description' => 'Descrição',
            'cost' => 'Custo',
            'date' => 'Data de Manutenção',
            'mileage_record' => 'Quilometragem',
            'next_date' => 'Próxima Data',
            'workshop' => 'Oficina',
            'status' => 'Estado',
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
            'vehicle_id',
            'type',
            'type_label' => function ($model) {
                return $this->getTypeLabel($model->type);
            },
            'description',
            'cost',
            'date',
            'mileage_record',
            'next_date',
            'workshop',
            'status',
            'status_label' => function ($model) {
                return $this->getStatusLabel($model->status);
            },
            'days_until_maintenance' => function ($model) {
                if ($model->date && $model->status === 'scheduled') {
                    $now = new \DateTime();
                    $maintenanceDate = new \DateTime($model->date);
                    return $maintenanceDate->diff($now)->days * ($maintenanceDate > $now ? 1 : -1);
                }
                return null;
            },
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
            'vehicle',
        ];
    }

    /**
     * Get vehicle relationship
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }

    /**
     * Get type label
     * 
     * @param string $type
     * @return string
     */
    public function getTypeLabel($type)
    {
        $labels = [
            'preventive' => 'Preventiva',
            'corrective' => 'Corretiva',
            'revision' => 'Revisão',
            'inspection' => 'Inspeção',
        ];

        return $labels[$type] ?? $type;
    }

    /**
     * Get status label
     * 
     * @param string $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        $labels = [
            'scheduled' => 'Agendada',
            'completed' => 'Concluída',
            'cancelled' => 'Cancelada',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Check if maintenance is overdue
     * 
     * @return boolean
     */
    public function isOverdue()
    {
        if ($this->status !== 'scheduled' || !$this->date) {
            return false;
        }

        return strtotime($this->date) < time();
    }

    /**
     * Check if maintenance is upcoming (within 7 days)
     * 
     * @return boolean
     */
    public function isUpcoming()
    {
        if ($this->status !== 'scheduled' || !$this->date) {
            return false;
        }

        $maintenanceTime = strtotime($this->date);
        $now = time();
        $sevenDaysFromNow = $now + (7 * 24 * 60 * 60);

        return $maintenanceTime >= $now && $maintenanceTime <= $sevenDaysFromNow;
    }
}
