<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use common\models\User;

/**
 * Route API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $vehicle_id
 * @property integer $driver_id
 * @property string $start_location
 * @property string $end_location
 * @property string $start_time
 * @property string $end_time
 * @property string $created_at
 * @property string $updated_at
 */
class Route extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%routes}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'vehicle_id', 'driver_id', 'start_location', 'end_location', 'start_time'], 'required'],
            [['company_id', 'vehicle_id', 'driver_id'], 'integer'],
            [['start_location', 'end_location'], 'string', 'max' => 255],
            [['start_time', 'end_time'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_id' => 'id']],
            [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['driver_id' => 'id']],
            // End time must be after start time
            [['end_time'], 'validateEndTime'],
        ];
    }

    /**
     * Validate that end time is after start time
     */
    public function validateEndTime($attribute, $params)
    {
        if ($this->end_time && $this->start_time) {
            if (strtotime($this->end_time) < strtotime($this->start_time)) {
                $this->addError($attribute, 'A hora de chegada deve ser posterior à hora de partida.');
            }
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
            'vehicle_id' => 'Veículo',
            'driver_id' => 'Condutor',
            'start_location' => 'Local de Partida',
            'end_location' => 'Local de Chegada',
            'start_time' => 'Hora de Partida',
            'end_time' => 'Hora de Chegada',
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
            'driver_id',
            'start_location',
            'end_location',
            'start_time',
            'end_time',
            'status' => function ($model) {
                return $this->getStatus();
            },
            'status_label' => function ($model) {
                return $this->getStatusLabel();
            },
            'duration' => function ($model) {
                return $this->getDuration();
            },
            'duration_formatted' => function ($model) {
                return $this->getDurationFormatted();
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
            'company',
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
     * Get route status
     */
    public function getStatus()
    {
        $now = time();
        $startTime = strtotime($this->start_time);
        $endTime = $this->end_time ? strtotime($this->end_time) : null;

        if ($endTime && $now > $endTime) {
            return 'completed';
        } elseif ($now >= $startTime && (!$endTime || $now < $endTime)) {
            return 'in_progress';
        } else {
            return 'scheduled';
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabel()
    {
        $status = $this->getStatus();
        $labels = [
            'scheduled' => 'Agendada',
            'in_progress' => 'Em Progresso',
            'completed' => 'Concluída',
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * Get duration in minutes
     */
    public function getDuration()
    {
        if (!$this->end_time) {
            return null;
        }
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        return round(($end - $start) / 60);
    }

    /**
     * Get duration formatted
     */
    public function getDurationFormatted()
    {
        $minutes = $this->getDuration();
        if ($minutes === null) {
            return null;
        }
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        if ($hours > 0) {
            return $hours . 'h ' . $mins . 'min';
        }
        return $mins . ' min';
    }

    /**
     * Complete route (set end time to now)
     */
    public function complete()
    {
        $this->end_time = date('Y-m-d H:i:s');
        return $this->save(false);
    }
}
