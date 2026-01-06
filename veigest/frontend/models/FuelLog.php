<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use common\models\User;

/**
 * Model para registos de combustível
 * Tabela: fuel_logs
 *
 * @property int $id
 * @property int $company_id
 * @property int $vehicle_id
 * @property int|null $driver_id
 * @property string $date
 * @property float $liters
 * @property float $value
 * @property float|null $price_per_liter
 * @property int|null $current_mileage
 * @property string|null $notes
 * @property string $created_at
 * 
 * @property Vehicle $vehicle
 * @property User $driver
 */
class FuelLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fuel_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'date', 'liters', 'value'], 'required'],
            [['company_id', 'vehicle_id', 'driver_id', 'current_mileage'], 'integer'],
            [['date'], 'safe'],
            [['liters', 'value', 'price_per_liter'], 'number'],
            [['notes'], 'string', 'max' => 255],
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
            'driver_id' => 'Condutor',
            'date' => 'Data',
            'liters' => 'Litros',
            'value' => 'Valor (€)',
            'price_per_liter' => 'Preço/Litro',
            'current_mileage' => 'Quilometragem',
            'notes' => 'Notas',
            'created_at' => 'Criado em',
        ];
    }

    /**
     * Relação com veículo
     * @return \yii\db\ActiveQuery
     */
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }

    /**
     * Relação com condutor
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }

    /**
     * Calcula automaticamente o preço por litro
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->liters > 0 && $this->value > 0) {
                $this->price_per_liter = round($this->value / $this->liters, 4);
            }
            if ($insert && empty($this->company_id)) {
                $this->company_id = Yii::$app->user->identity->company_id;
            }
            return true;
        }
        return false;
    }
}
