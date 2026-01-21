<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Vehicle API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $license_plate
 * @property string $brand
 * @property string $model
 * @property integer $year
 * @property string $fuel_type
 * @property integer $mileage
 * @property string $status
 * @property integer $driver_id
 * @property string $photo
 * @property string $created_at
 * @property string $updated_at
 */
class Vehicle extends ActiveRecord
{
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
            [['company_id', 'license_plate', 'brand', 'model'], 'required'],
            [['company_id', 'year', 'mileage', 'driver_id'], 'integer'],
            [['license_plate'], 'string', 'max' => 20],
            [['brand', 'model'], 'string', 'max' => 100],
            [['fuel_type'], 'in', 'range' => ['gasoline', 'diesel', 'electric', 'hybrid', 'other']],
            [['fuel_type'], 'default', 'value' => 'gasoline'],
            [['status'], 'in', 'range' => ['active', 'maintenance', 'inactive']],
            [['status'], 'default', 'value' => 'active'],
            [['photo'], 'string', 'max' => 255],
            [['mileage'], 'default', 'value' => 0],
            [['year'], 'integer', 'min' => 1900, 'max' => 2030],
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
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'company_id',
            'license_plate',
            'brand',
            'model',
            'year',
            'fuel_type',
            'mileage',
            'status',
            'driver_id',
            'photo',
            'fuel_type_label' => function ($model) {
                return $this->getFuelTypeLabel($model->fuel_type);
            },
            'status_label' => function ($model) {
                return $this->getStatusLabel($model->status);
            },
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Extra fields for expand parameter
     */
    public function extraFields()
    {
        return [
            'company',
            'driver',
            'maintenances',
            'fuelLogs',
            'recentMaintenances' => function ($model) {
                return $model->getMaintenances()
                    ->orderBy(['data_manutencao' => SORT_DESC])
                    ->limit(5)
                    ->all();
            },
            'recentFuelLogs' => function ($model) {
                return $model->getFuelLogs()
                    ->orderBy(['data_abastecimento' => SORT_DESC])
                    ->limit(5)
                    ->all();
            },
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
     * Get driver relationship
     */
    public function getDriver()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'driver_id']);
    }

    /**
     * Get maintenances relationship
     */
    public function getMaintenances()
    {
        return $this->hasMany(Maintenance::class, ['vehicle_id' => 'id']);
    }

    /**
     * Get fuel logs relationship
     */
    public function getFuelLogs()
    {
        return $this->hasMany(FuelLog::class, ['vehicle_id' => 'id']);
    }

    /**
     * Get fuel type label
     * 
     * @param string $fuelType
     * @return string
     */
    public function getFuelTypeLabel($fuelType)
    {
        $labels = [
            'gasoline' => 'Gasolina',
            'diesel' => 'Diesel',
            'electric' => 'Elétrico',
            'hybrid' => 'Híbrido',
            'other' => 'Outro',
        ];

        return $labels[$fuelType] ?? $fuelType;
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
            'active' => 'Ativo',
            'maintenance' => 'Em Manutenção',
            'inactive' => 'Inativo',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get current driver name
     * 
     * @return string|null
     */
    public function getDriverName()
    {
        return $this->driver ? ($this->driver->name ?? $this->driver->nome) : null;
    }

    /**
     * Check if vehicle needs maintenance
     * 
     * @return boolean
     */
    public function needsMaintenance()
    {
        // Verificar se há manutenções agendadas
        $scheduledMaintenance = $this->getMaintenances()
            ->where(['estado' => 'agendada'])
            ->andWhere(['<=', 'data_manutencao', date('Y-m-d')])
            ->exists();

        return $scheduledMaintenance;
    }

    /**
     * Get average fuel consumption (L/100km)
     * 
     * @return float
     */
    public function getAverageFuelConsumption()
    {
        $fuelLogs = $this->getFuelLogs()
            ->orderBy(['quilometragem' => SORT_ASC])
            ->all();

        if (count($fuelLogs) < 2) {
            return 0;
        }

        $totalDistance = 0;
        $totalLiters = 0;

        for ($i = 1; $i < count($fuelLogs); $i++) {
            $distance = $fuelLogs[$i]->quilometragem - $fuelLogs[$i - 1]->quilometragem;
            if ($distance > 0) {
                $totalDistance += $distance;
                $totalLiters += $fuelLogs[$i]->litros;
            }
        }

        return $totalDistance > 0 ? round(($totalLiters / $totalDistance) * 100, 2) : 0;
    }
}
