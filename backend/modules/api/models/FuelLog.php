<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * FuelLog API model
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property double $liters
 * @property double $value
 * @property integer $current_mileage
 * @property string $date
 * @property double $price_per_liter
 * @property string $notes
 * @property string $created_at
 * @property string $updated_at
 */
class FuelLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fuel_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'liters', 'value'], 'required'],
            [['vehicle_id', 'current_mileage'], 'integer'],
            [['liters', 'value', 'price_per_liter'], 'number', 'min' => 0],
            [['date'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            [
                ['date'],
                'default',
                'value' => function () {
                    return date('Y-m-d H:i:s');
                }
            ],
            [['notes'], 'string'],
            [['current_mileage'], 'integer', 'min' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'vehicle_id' => 'Veículo',
            'liters' => 'Litros',
            'value' => 'Custo Total',
            'current_mileage' => 'Quilometragem',
            'date' => 'Data de Abastecimento',
            'price_per_liter' => 'Preço por Litro',
            'notes' => 'Observações',
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
            'vehicle_id',
            'liters',
            'value',
            'current_mileage',
            'date',
            'price_per_liter' => function ($model) {
                return $model->liters > 0 ? round($model->value / $model->liters, 3) : 0;
            },
            'notes',
            'consumption_since_last' => function ($model) {
                return $this->getConsumptionSinceLast();
            },
            'cost_per_km' => function ($model) {
                $consumption = $this->getConsumptionSinceLast();
                return $consumption > 0 ? round($model->value / $consumption, 3) : 0;
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
     * Before save event
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calcular preço por litro automaticamente se não fornecido
            if ($this->liters > 0 && $this->value > 0) {
                $this->price_per_liter = round($this->value / $this->liters, 3);
            }

            return true;
        }
        return false;
    }

    /**
     * Get consumption since last fuel log
     * 
     * @return float Distance in km
     */
    public function getConsumptionSinceLast()
    {
        if (!$this->current_mileage) {
            return 0;
        }

        $lastFuelLog = static::find()
            ->where(['vehicle_id' => $this->vehicle_id])
            ->andWhere(['<', 'current_mileage', $this->current_mileage])
            ->orderBy(['current_mileage' => SORT_DESC])
            ->one();

        if (!$lastFuelLog || !$lastFuelLog->current_mileage) {
            return 0;
        }

        return $this->current_mileage - $lastFuelLog->current_mileage;
    }

    /**
     * Calculate fuel efficiency (km/l)
     * 
     * @return float
     */
    public function getFuelEfficiency()
    {
        $distance = $this->getConsumptionSinceLast();
        return ($distance > 0 && $this->liters > 0) ? round($distance / $this->liters, 2) : 0;
    }

    /**
     * Get average fuel price per liter for this vehicle
     * 
     * @return float
     */
    public function getAverageFuelPrice()
    {
        $avgPrice = static::find()
            ->where(['vehicle_id' => $this->vehicle_id])
            ->andWhere(['>', 'liters', 0])
            ->andWhere(['>', 'value', 0])
            ->average('(value / liters)');

        return $avgPrice ? round($avgPrice, 3) : 0;
    }

    /**
     * Check if this is an expensive fill-up compared to average
     * 
     * @return boolean
     */
    public function isExpensive()
    {
        $currentPrice = $this->liters > 0 ? ($this->value / $this->liters) : 0;
        $averagePrice = $this->getAverageFuelPrice();

        return $currentPrice > 0 && $averagePrice > 0 && $currentPrice > ($averagePrice * 1.1); // 10% above average
    }
}
