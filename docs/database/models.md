# 📊 Models ActiveRecord

## Visão Geral

Os models ActiveRecord representam tabelas da base de dados e estão em `common/models/`. Cada model tem validação, relações e métodos de negócio.

## Model Base

### Estrutura Padrão

```php
<?php
// common/models/Vehicle.php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Vehicle model
 *
 * @property int $id
 * @property int $company_id
 * @property string $license_plate
 * @property string $brand
 * @property string $model
 * @property int|null $year
 * @property string|null $color
 * @property string $fuel_type
 * @property string $status
 * @property int|null $assigned_driver_id
 * @property int $current_mileage
 * @property string|null $insurance_expiry
 * @property string|null $inspection_expiry
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 * @property User $assignedDriver
 * @property Maintenance[] $maintenances
 * @property FuelLog[] $fuelLogs
 * @property Document[] $documents
 */
class Vehicle extends ActiveRecord
{
    // Constantes de status
    const STATUS_ACTIVE = 'active';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SOLD = 'sold';
    
    // Constantes de combustível
    const FUEL_GASOLINE = 'gasoline';
    const FUEL_DIESEL = 'diesel';
    const FUEL_ELECTRIC = 'electric';
    const FUEL_HYBRID = 'hybrid';
    const FUEL_LPG = 'lpg';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%vehicle}}';
    }
    
    /**
     * Behaviors - timestamps automáticos
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Obrigatórios
            [['company_id', 'license_plate', 'brand', 'model'], 'required'],
            
            // Tipos
            [['company_id', 'year', 'assigned_driver_id', 'current_mileage'], 'integer'],
            [['tank_capacity', 'purchase_price'], 'number'],
            [['notes'], 'string'],
            [['insurance_expiry', 'inspection_expiry', 'purchase_date'], 'safe'],
            
            // Tamanhos
            [['license_plate'], 'string', 'max' => 20],
            [['brand', 'model'], 'string', 'max' => 100],
            [['color', 'vin'], 'string', 'max' => 50],
            
            // Valores permitidos
            [['status'], 'in', 'range' => [
                self::STATUS_ACTIVE, 
                self::STATUS_MAINTENANCE, 
                self::STATUS_INACTIVE,
                self::STATUS_SOLD
            ]],
            [['fuel_type'], 'in', 'range' => [
                self::FUEL_GASOLINE,
                self::FUEL_DIESEL,
                self::FUEL_ELECTRIC,
                self::FUEL_HYBRID,
                self::FUEL_LPG
            ]],
            
            // Valores default
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['fuel_type'], 'default', 'value' => self::FUEL_DIESEL],
            [['current_mileage'], 'default', 'value' => 0],
            
            // Unicidade
            [['license_plate'], 'unique', 'targetAttribute' => ['license_plate', 'company_id'],
             'message' => 'Esta matrícula já existe nesta empresa'],
            
            // FK existência
            [['company_id'], 'exist', 'skipOnError' => true,
             'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['assigned_driver_id'], 'exist', 'skipOnError' => true,
             'targetClass' => User::class, 'targetAttribute' => ['assigned_driver_id' => 'id']],
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
            'color' => 'Cor',
            'fuel_type' => 'Combustível',
            'tank_capacity' => 'Capacidade Depósito (L)',
            'current_mileage' => 'Quilometragem',
            'status' => 'Estado',
            'assigned_driver_id' => 'Condutor Atribuído',
            'insurance_expiry' => 'Validade Seguro',
            'inspection_expiry' => 'Validade Inspeção',
            'created_at' => 'Criado Em',
            'updated_at' => 'Actualizado Em',
        ];
    }
    
    // ==================== RELAÇÕES ====================
    
    /**
     * Relação com Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
    
    /**
     * Relação com User (condutor atribuído)
     */
    public function getAssignedDriver()
    {
        return $this->hasOne(User::class, ['id' => 'assigned_driver_id']);
    }
    
    /**
     * Relação com Maintenance
     */
    public function getMaintenances()
    {
        return $this->hasMany(Maintenance::class, ['vehicle_id' => 'id'])
            ->orderBy(['date' => SORT_DESC]);
    }
    
    /**
     * Relação com FuelLog
     */
    public function getFuelLogs()
    {
        return $this->hasMany(FuelLog::class, ['vehicle_id' => 'id'])
            ->orderBy(['date' => SORT_DESC]);
    }
    
    /**
     * Relação com Document
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['vehicle_id' => 'id']);
    }
    
    /**
     * Relação com Alert
     */
    public function getAlerts()
    {
        return $this->hasMany(Alert::class, ['vehicle_id' => 'id']);
    }
    
    // ==================== SCOPES/QUERIES ====================
    
    /**
     * Scope: Apenas veículos activos
     */
    public static function findActive()
    {
        return self::find()->where(['status' => self::STATUS_ACTIVE]);
    }
    
    /**
     * Scope: Por empresa
     */
    public static function findByCompany($companyId)
    {
        return self::find()->where(['company_id' => $companyId]);
    }
    
    // ==================== MÉTODOS DE NEGÓCIO ====================
    
    /**
     * Obter nome completo (marca + modelo)
     */
    public function getFullName()
    {
        return $this->brand . ' ' . $this->model;
    }
    
    /**
     * Verificar se precisa de inspeção
     */
    public function needsInspection($daysAhead = 30)
    {
        if (!$this->inspection_expiry) {
            return false;
        }
        
        $expiryDate = strtotime($this->inspection_expiry);
        $warningDate = strtotime("+{$daysAhead} days");
        
        return $expiryDate <= $warningDate;
    }
    
    /**
     * Verificar se seguro está a expirar
     */
    public function needsInsuranceRenewal($daysAhead = 30)
    {
        if (!$this->insurance_expiry) {
            return false;
        }
        
        $expiryDate = strtotime($this->insurance_expiry);
        $warningDate = strtotime("+{$daysAhead} days");
        
        return $expiryDate <= $warningDate;
    }
    
    /**
     * Calcular consumo médio (L/100km)
     */
    public function getAverageConsumption()
    {
        $fuelLogs = $this->getFuelLogs()
            ->where(['full_tank' => 1])
            ->orderBy(['date' => SORT_ASC])
            ->all();
        
        if (count($fuelLogs) < 2) {
            return null;
        }
        
        $totalLiters = 0;
        $totalKm = 0;
        
        for ($i = 1; $i < count($fuelLogs); $i++) {
            $totalLiters += $fuelLogs[$i]->liters;
            $totalKm += $fuelLogs[$i]->mileage - $fuelLogs[$i - 1]->mileage;
        }
        
        if ($totalKm <= 0) {
            return null;
        }
        
        return round(($totalLiters / $totalKm) * 100, 2);
    }
    
    /**
     * Obter custo total de manutenção
     */
    public function getTotalMaintenanceCost($year = null)
    {
        $query = $this->getMaintenances();
        
        if ($year) {
            $query->andWhere(['YEAR(date)' => $year]);
        }
        
        return $query->sum('cost') ?? 0;
    }
    
    /**
     * Obter custo total de combustível
     */
    public function getTotalFuelCost($year = null)
    {
        $query = $this->getFuelLogs();
        
        if ($year) {
            $query->andWhere(['YEAR(date)' => $year]);
        }
        
        return $query->sum('total_cost') ?? 0;
    }
    
    /**
     * Actualizar quilometragem
     */
    public function updateMileage($newMileage)
    {
        if ($newMileage > $this->current_mileage) {
            $this->current_mileage = $newMileage;
            return $this->save(false, ['current_mileage', 'updated_at']);
        }
        return false;
    }
    
    /**
     * Obter lista de status para dropdown
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_MAINTENANCE => 'Em Manutenção',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_SOLD => 'Vendido',
        ];
    }
    
    /**
     * Obter lista de combustíveis para dropdown
     */
    public static function getFuelTypeList()
    {
        return [
            self::FUEL_GASOLINE => 'Gasolina',
            self::FUEL_DIESEL => 'Diesel',
            self::FUEL_ELECTRIC => 'Eléctrico',
            self::FUEL_HYBRID => 'Híbrido',
            self::FUEL_LPG => 'GPL',
        ];
    }
    
    /**
     * Label do status actual
     */
    public function getStatusLabel()
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }
    
    /**
     * Label do tipo de combustível
     */
    public function getFuelTypeLabel()
    {
        return self::getFuelTypeList()[$this->fuel_type] ?? $this->fuel_type;
    }
}
```

---

## User Model

### `common/models/User.php`

```php
<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $username
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $role
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Company $company
 * @property Vehicle[] $assignedVehicles
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;
    
    const ROLE_ADMIN = 'admin';
    const ROLE_GESTOR = 'gestor';
    const ROLE_CONDUTOR = 'condutor';
    
    public static function tableName()
    {
        return '{{%user}}';
    }
    
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            ['role', 'in', 'range' => [self::ROLE_ADMIN, self::ROLE_GESTOR, self::ROLE_CONDUTOR]],
            ['role', 'default', 'value' => self::ROLE_CONDUTOR],
            [['username', 'email'], 'required'],
            [['username', 'email'], 'unique'],
            ['email', 'email'],
        ];
    }
    
    // ==================== IDENTITY INTERFACE ====================
    
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // Para API: token em Base64
        $decoded = base64_decode($token);
        if ($decoded === false) {
            return null;
        }
        
        $parts = explode(':', $decoded);
        if (count($parts) !== 2) {
            return null;
        }
        
        [$userId, $authKey] = $parts;
        
        return static::findOne([
            'id' => $userId,
            'auth_key' => $authKey,
            'status' => self::STATUS_ACTIVE,
        ]);
    }
    
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }
    
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    
    public function getAuthKey()
    {
        return $this->auth_key;
    }
    
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }
    
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
    
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }
    
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    // ==================== RELAÇÕES ====================
    
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
    
    public function getAssignedVehicles()
    {
        return $this->hasMany(Vehicle::class, ['assigned_driver_id' => 'id']);
    }
    
    // ==================== MÉTODOS ====================
    
    public function generateApiToken()
    {
        return base64_encode($this->id . ':' . $this->auth_key);
    }
    
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }
    
    public function isGestor()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_GESTOR]);
    }
    
    public function getActiveAlertsCount()
    {
        return Alert::find()
            ->where(['company_id' => $this->company_id, 'status' => 'active'])
            ->count();
    }
    
    public static function getRoleList()
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_GESTOR => 'Gestor',
            self::ROLE_CONDUTOR => 'Condutor',
        ];
    }
}
```

---

## Maintenance Model

```php
<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Maintenance extends ActiveRecord
{
    const TYPE_PREVENTIVE = 'preventive';
    const TYPE_CORRECTIVE = 'corrective';
    const TYPE_INSPECTION = 'inspection';
    const TYPE_TIRES = 'tires';
    const TYPE_OIL_CHANGE = 'oil_change';
    const TYPE_OTHER = 'other';
    
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    
    public static function tableName()
    {
        return '{{%maintenance}}';
    }
    
    public function rules()
    {
        return [
            [['company_id', 'vehicle_id', 'description', 'date'], 'required'],
            [['company_id', 'vehicle_id', 'mileage_at_service', 'next_service_mileage', 'created_by'], 'integer'],
            [['cost'], 'number', 'min' => 0],
            [['description', 'notes'], 'string'],
            [['date', 'next_service_date'], 'safe'],
            [['type'], 'in', 'range' => array_keys(self::getTypeList())],
            [['status'], 'in', 'range' => array_keys(self::getStatusList())],
            [['workshop', 'invoice_number'], 'string', 'max' => 255],
            [['type'], 'default', 'value' => self::TYPE_PREVENTIVE],
            [['status'], 'default', 'value' => self::STATUS_SCHEDULED],
            [['cost'], 'default', 'value' => 0],
        ];
    }
    
    // Relações
    public function getVehicle()
    {
        return $this->hasOne(Vehicle::class, ['id' => 'vehicle_id']);
    }
    
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
    
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    
    // Métodos estáticos para relatórios
    public static function getStatsByCompany($companyId)
    {
        return [
            'total' => self::find()->where(['company_id' => $companyId])->count(),
            'completed' => self::find()->where(['company_id' => $companyId, 'status' => self::STATUS_COMPLETED])->count(),
            'scheduled' => self::find()->where(['company_id' => $companyId, 'status' => self::STATUS_SCHEDULED])->count(),
            'total_cost' => self::find()->where(['company_id' => $companyId])->sum('cost') ?? 0,
        ];
    }
    
    public static function getMonthlyCosts($companyId, $months = 12)
    {
        return self::find()
            ->select([
                'DATE_FORMAT(date, "%Y-%m") as month',
                'SUM(cost) as total_cost',
                'COUNT(*) as count',
            ])
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'date', date('Y-m-d', strtotime("-{$months} months"))])
            ->groupBy(['month'])
            ->orderBy(['month' => SORT_ASC])
            ->asArray()
            ->all();
    }
    
    public static function getCostsByType($companyId)
    {
        return self::find()
            ->select(['type', 'SUM(cost) as total_cost'])
            ->where(['company_id' => $companyId])
            ->groupBy(['type'])
            ->indexBy('type')
            ->asArray()
            ->all();
    }
    
    public static function getUpcoming($companyId, $days = 30)
    {
        return self::find()
            ->where(['company_id' => $companyId, 'status' => self::STATUS_SCHEDULED])
            ->andWhere(['<=', 'date', date('Y-m-d', strtotime("+{$days} days"))])
            ->orderBy(['date' => SORT_ASC])
            ->all();
    }
    
    public static function getTypeList()
    {
        return [
            self::TYPE_PREVENTIVE => 'Preventiva',
            self::TYPE_CORRECTIVE => 'Corretiva',
            self::TYPE_INSPECTION => 'Inspeção',
            self::TYPE_TIRES => 'Pneus',
            self::TYPE_OIL_CHANGE => 'Mudança de Óleo',
            self::TYPE_OTHER => 'Outro',
        ];
    }
    
    public static function getStatusList()
    {
        return [
            self::STATUS_SCHEDULED => 'Agendada',
            self::STATUS_IN_PROGRESS => 'Em Curso',
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }
}
```

---

## Queries Comuns

### Básicas

```php
// Encontrar por ID
$vehicle = Vehicle::findOne(1);
$vehicle = Vehicle::findOne(['id' => 1, 'status' => 'active']);

// Encontrar todos
$vehicles = Vehicle::find()->all();

// Com condições
$vehicles = Vehicle::find()
    ->where(['company_id' => $companyId])
    ->andWhere(['status' => 'active'])
    ->orderBy(['brand' => SORT_ASC])
    ->limit(10)
    ->all();

// Count
$count = Vehicle::find()->where(['company_id' => $companyId])->count();

// Exists
$exists = Vehicle::find()->where(['license_plate' => 'AA-00-AA'])->exists();
```

### Com Relações (Eager Loading)

```php
// Carregar com relações para evitar N+1
$vehicles = Vehicle::find()
    ->with(['company', 'assignedDriver', 'maintenances'])
    ->where(['company_id' => $companyId])
    ->all();

// Usar relações
foreach ($vehicles as $vehicle) {
    echo $vehicle->company->name;  // Não faz query adicional
    echo $vehicle->assignedDriver?->username;
}
```

### Agregações

```php
// Sum
$totalCost = Maintenance::find()
    ->where(['company_id' => $companyId])
    ->sum('cost');

// Average
$avgCost = Maintenance::find()
    ->where(['company_id' => $companyId])
    ->average('cost');

// Max/Min
$maxMileage = Vehicle::find()
    ->where(['company_id' => $companyId])
    ->max('current_mileage');
```

### Batch Operations

```php
// Update em batch
Vehicle::updateAll(
    ['status' => 'inactive'],
    ['company_id' => $companyId, 'status' => 'active']
);

// Delete em batch
Maintenance::deleteAll(['status' => 'cancelled']);
```

---

## Próximos Passos

- [Schema](schema.md)
- [Migrations](migrations.md)
