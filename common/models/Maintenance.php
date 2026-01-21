<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "maintenances".
 *
 * @property int $id
 * @property int $company_id
 * @property int $vehicle_id
 * @property string $type
 * @property string|null $description
 * @property string $date
 * @property float $cost
 * @property int|null $mileage_record
 * @property string|null $next_date
 * @property string|null $workshop
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property Company $company
 * @property Vehicle $vehicle
 */
class Maintenance extends ActiveRecord
{
    // Constantes de status de manutenção
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Constantes de tipos de manutenção
    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_OIL_CHANGE = 'oil_change';
    const TYPE_TIRE = 'tire';
    const TYPE_BRAKE = 'brake';
    const TYPE_OTHER = 'other';

    /**
     * Retorna os labels dos status de manutenção
     * 
     * @return array
     */
    public static function getStatusLabels()
    {
        return [
            self::STATUS_SCHEDULED => 'Agendada',
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }

    /**
     * Retorna os labels dos tipos de manutenção
     * 
     * @return array
     */
    public static function getTypeLabels()
    {
        return [
            self::TYPE_PREVENTIVE => 'Preventiva',
            self::TYPE_CORRECTIVE => 'Corretiva',
            self::TYPE_INSPECTION => 'Inspeção',
            self::TYPE_OIL_CHANGE => 'Troca de Óleo',
            self::TYPE_TIRE => 'Pneus',
            self::TYPE_BRAKE => 'Travões',
            self::TYPE_OTHER => 'Outros',
        ];
    }

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
            [['company_id', 'vehicle_id', 'type', 'date'], 'required'],
            [['company_id', 'vehicle_id', 'mileage_record'], 'integer'],
            [['cost'], 'number'],
            [['cost'], 'default', 'value' => 0],
            [['description'], 'string'],
            [['date', 'next_date', 'created_at', 'updated_at'], 'safe'],
            [['type'], 'string', 'max' => 100],
            [['workshop'], 'string', 'max' => 200],
            [['status'], 'string', 'max' => 50],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['vehicle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Vehicle::class, 'targetAttribute' => ['vehicle_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company',
            'vehicle_id' => 'Vehicle',
            'type' => 'Type',
            'description' => 'Description',
            'date' => 'Date',
            'cost' => 'Cost',
            'mileage_record' => 'Mileage',
            'next_date' => 'Next Maintenance',
            'workshop' => 'Workshop',
            'status' => 'Status',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
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
     * Retorna lista de tipos de manutenção
     *
     * @return array
     */
    public static function getTypesList()
    {
        return [
            self::TYPE_PREVENTIVE => 'Preventiva',
            self::TYPE_CORRECTIVE => 'Corretiva',
            self::TYPE_INSPECTION => 'Inspeção',
            self::TYPE_OIL_CHANGE => 'Troca de Óleo',
            self::TYPE_TIRE => 'Pneus',
            self::TYPE_BRAKE => 'Travões',
            self::TYPE_OTHER => 'Outro',
        ];
    }

    /**
     * Retorna o label do tipo
     *
     * @return string
     */
    public function getTypeLabel()
    {
        $types = self::getTypesList();
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Retorna estatísticas de manutenção por empresa e período
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

        $totalCost = (float) $query->sum('cost') ?: 0;
        $totalRecords = (int) $query->count();

        // Manutenções agendadas (próximas)
        $upcoming = self::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>', 'next_date', date('Y-m-d')])
            ->andWhere(['<=', 'next_date', date('Y-m-d', strtotime('+30 days'))])
            ->count();

        return [
            'total_cost' => round($totalCost, 2),
            'total_records' => $totalRecords,
            'upcoming' => (int) $upcoming,
            'avg_cost' => $totalRecords > 0 ? round($totalCost / $totalRecords, 2) : 0,
        ];
    }

    /**
     * Retorna custos mensais de manutenção
     *
     * @param int $companyId
     * @param int $months
     * @return array
     */
    public static function getMonthlyCosts($companyId, $months = 6)
    {
        $startDate = date('Y-m-01', strtotime("-{$months} months"));
        
        return Yii::$app->db->createCommand("
            SELECT 
                DATE_FORMAT(date, '%Y-%m') as month,
                DATE_FORMAT(date, '%b/%Y') as month_label,
                SUM(cost) as total_cost,
                COUNT(*) as count
            FROM {{%maintenances}}
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
     * Retorna custos por tipo de manutenção
     *
     * @param int $companyId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getCostsByType($companyId, $startDate = null, $endDate = null)
    {
        $query = "
            SELECT 
                type as type,
                SUM(cost) as total_cost,
                COUNT(*) as count,
                AVG(cost) as avg_cost
            FROM {{%maintenances}}
            WHERE company_id = :companyId
        ";
        
        $params = [':companyId' => $companyId];
        
        if ($startDate) {
            $query .= " AND date >= :startDate";
            $params[':startDate'] = $startDate;
        }
        if ($endDate) {
            $query .= " AND date <= :endDate";
            $params[':endDate'] = $endDate;
        }
        
        $query .= " GROUP BY type ORDER BY total_cost DESC";
        
        return Yii::$app->db->createCommand($query)->bindValues($params)->queryAll();
    }

    /**
     * Retorna custos por veículo
     *
     * @param int $companyId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public static function getCostsByVehicle($companyId, $startDate = null, $endDate = null)
    {
        $query = "
            SELECT 
                v.id as vehicle_id,
                v.license_plate,
                v.brand,
                v.model,
                COALESCE(SUM(m.cost), 0) as total_cost,
                COUNT(m.id) as maintenance_count,
                COALESCE(AVG(m.cost), 0) as avg_cost
            FROM {{%vehicles}} v
            LEFT JOIN {{%maintenances}} m ON v.id = m.vehicle_id
        ";
        
        $params = [':companyId' => $companyId];
        $where = "WHERE v.company_id = :companyId";
        
        if ($startDate) {
            $where .= " AND (m.date IS NULL OR m.date >= :startDate)";
            $params[':startDate'] = $startDate;
        }
        if ($endDate) {
            $where .= " AND (m.date IS NULL OR m.date <= :endDate)";
            $params[':endDate'] = $endDate;
        }
        
        $query .= " {$where} GROUP BY v.id ORDER BY total_cost DESC";
        
        return Yii::$app->db->createCommand($query)->bindValues($params)->queryAll();
    }

    /**
     * Retorna próximas manutenções agendadas
     *
     * @param int $companyId
     * @param int $days
     * @return array
     */
    public static function getUpcoming($companyId, $days = 30)
    {
        return self::find()
            ->alias('m')
            ->select(['m.*', 'v.license_plate', 'v.brand', 'v.model'])
            ->leftJoin('{{%vehicles}} v', 'm.vehicle_id = v.id')
            ->where(['m.company_id' => $companyId])
            ->andWhere(['>', 'm.next_date', date('Y-m-d')])
            ->andWhere(['<=', 'm.next_date', date('Y-m-d', strtotime("+{$days} days"))])
            ->orderBy(['m.next_date' => SORT_ASC])
            ->asArray()
            ->all();
    }
}
