<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use common\models\User;

/**
 * Simple administrative route record
 * Table: routes
 *
 * Fields: id, company_id, vehicle_id, driver_id, start_location, end_location,
 *         start_time, end_time, created_at, updated_at
 */
class Route extends ActiveRecord
{
    public static function tableName()
    {
        return 'routes';
    }

    public function rules()
    {
        return [
            [['company_id', 'vehicle_id', 'driver_id', 'start_location', 'end_location', 'start_time'], 'required'],
            [['company_id', 'vehicle_id', 'driver_id'], 'integer'],
            [['start_time', 'end_time'], 'safe'],
            [['start_location', 'end_location'], 'string', 'max' => 255],
            // Ensure driver belongs to same company (controller will set company_id)
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'vehicle_id' => 'Veículo',
            'driver_id' => 'Condutor',
            'start_location' => 'Origem',
            'end_location' => 'Destino',
            'start_time' => 'Data/Hora Início',
            'end_time' => 'Data/Hora Fim',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    public function getDriver()
    {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }

    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }
}
