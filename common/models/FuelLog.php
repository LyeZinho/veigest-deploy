<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "fuel_logs".
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
 * @property Company $company
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
        return '{{%fuel_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'vehicle_id', 'date', 'liters', 'value'], 'required'],
            [['company_id', 'vehicle_id', 'driver_id', 'current_mileage'], 'integer'],
            [['liters', 'value', 'price_per_liter'], 'number'],
            [['date', 'created_at'], 'safe'],
            [['notes'], 'string', 'max' => 255],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_id' => 'id']],
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
            'vehicle_id' => 'Veículo',
            'driver_id' => 'Motorista',
            'date' => 'Data',
            'liters' => 'Litros',
            'value' => 'Valor',
            'price_per_liter' => 'Preço por Litro',
            'current_mileage' => 'Quilometragem Atual',
            'notes' => 'Observações',
            'created_at' => 'Criado em',
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
     * Calcula automaticamente o preço por litro antes de salvar
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->liters > 0 && empty($this->price_per_liter)) {
                $this->price_per_liter = $this->value / $this->liters;
            }
            return true;
        }
        return false;
    }

    /**
     * Retorna estatísticas de combustível por empresa e período
     *
     * @param int $companyId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getStatsByCompany($companyId, $startDate = null, $endDate = null)
    {
        $query = self::find()->where(['company_id' => $companyId]);

        if ($startDate) {
            $query->andWhere(['>=', 'date', $startDate]);
        }
        if ($endDate) {
            $query->andWhere(['<=', 'date', $endDate]);
        }

        $totalLiters = (float) $query->sum('liters') ?: 0;
        $totalValue = (float) $query->sum('value') ?: 0;
        $totalRecords = (int) $query->count();

        return [
            'total_liters' => round($totalLiters, 2),
            'total_value' => round($totalValue, 2),
            'total_records' => $totalRecords,
            'avg_price_per_liter' => $totalLiters > 0 ? round($totalValue / $totalLiters, 4) : 0,
        ];
    }

    /**
     * Retorna consumo mensal por empresa
     *
     * @param int $companyId
     * @param int $months Número de meses a retornar
     * @return array
     */
    public static function getMonthlyConsumption($companyId, $months = 6)
    {
        $startDate = date('Y-m-01', strtotime("-{$months} months"));
        
        return Yii::$app->db->createCommand("
            SELECT 
                DATE_FORMAT(date, '%Y-%m') as month,
                DATE_FORMAT(date, '%b/%Y') as month_label,
                SUM(liters) as total_liters,
                SUM(value) as total_value,
                COUNT(*) as count
            FROM {{%fuel_logs}}
            WHERE company_id = :companyId 
              AND date >= :startDate
            GROUP BY DATE_FORMAT(date, '%Y-%m')
            ORDER BY month ASC
        ")->bindValues([
            ':companyId' => $companyId,
            ':startDate' => $startDate,
        ])->queryAll();
    }

    /**
     * Retorna consumo por veículo
     *
     * @param int $companyId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getConsumptionByVehicle($companyId, $startDate = null, $endDate = null)
    {
        $query = "
            SELECT 
                v.id as vehicle_id,
                v.license_plate,
                v.brand,
                v.model,
                COALESCE(SUM(fl.liters), 0) as total_liters,
                COALESCE(SUM(fl.value), 0) as total_value,
                COUNT(fl.id) as refuel_count
            FROM {{%vehicles}} v
            LEFT JOIN {{%fuel_logs}} fl ON v.id = fl.vehicle_id
        ";
        
        $params = [':companyId' => $companyId];
        $where = "WHERE v.company_id = :companyId";
        
        if ($startDate) {
            $where .= " AND (fl.date IS NULL OR fl.date >= :startDate)";
            $params[':startDate'] = $startDate;
        }
        if ($endDate) {
            $where .= " AND (fl.date IS NULL OR fl.date <= :endDate)";
            $params[':endDate'] = $endDate;
        }
        
        $query .= " {$where} GROUP BY v.id ORDER BY total_value DESC";
        
        return Yii::$app->db->createCommand($query)->bindValues($params)->queryAll();
    }
}
