# 📊 Models da API

## Visão Geral

Os models da API estão em `backend/modules/api/models/` e extendem `yii\db\ActiveRecord`. Eles definem a estrutura de dados, validações e relacionamentos.

## Estrutura Base de um Model

```php
<?php
namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Vehicle extends ActiveRecord
{
    // 1. Nome da tabela
    public static function tableName()
    {
        return '{{%vehicles}}';
    }
    
    // 2. Behaviors (timestamps automáticos)
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    
    // 3. Regras de validação
    public function rules()
    {
        return [
            // Campos obrigatórios
            [['company_id', 'license_plate', 'brand', 'model'], 'required'],
            // Tipos
            [['company_id', 'year', 'mileage', 'driver_id'], 'integer'],
            [['license_plate'], 'string', 'max' => 20],
            [['brand', 'model'], 'string', 'max' => 100],
            // Valores permitidos
            [['status'], 'in', 'range' => ['active', 'maintenance', 'inactive']],
            [['fuel_type'], 'in', 'range' => ['gasoline', 'diesel', 'electric', 'hybrid']],
            // Unicidade
            [['license_plate'], 'unique'],
            // Existência em tabelas relacionadas
            [['company_id'], 'exist', 'targetClass' => Company::class, 'targetAttribute' => 'id'],
        ];
    }
    
    // 4. Labels para atributos
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
        ];
    }
    
    // 5. Campos retornados na API
    public function fields()
    {
        return [
            'id',
            'license_plate',
            'brand',
            'model',
            'year',
            'fuel_type',
            'mileage',
            'status',
            'created_at' => function ($model) {
                return date('Y-m-d H:i:s', $model->created_at);
            },
        ];
    }
    
    // 6. Campos expandidos (com ?expand=)
    public function extraFields()
    {
        return [
            'company',
            'driver',
            'maintenances',
            'fuelLogs',
        ];
    }
    
    // 7. Relacionamentos
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
    
    public function getDriver()
    {
        return $this->hasOne(User::class, ['id' => 'driver_id']);
    }
    
    public function getMaintenances()
    {
        return $this->hasMany(Maintenance::class, ['vehicle_id' => 'id']);
    }
    
    public function getFuelLogs()
    {
        return $this->hasMany(FuelLog::class, ['vehicle_id' => 'id']);
    }
}
```

---

## Models Disponíveis

### Vehicle (Veículos)

```php
// Propriedades
$id             // int - ID único
$company_id     // int - FK para companies
$license_plate  // string - Matrícula (única)
$brand          // string - Marca
$model          // string - Modelo
$year           // int - Ano de fabrico
$fuel_type      // enum - gasoline, diesel, electric, hybrid
$mileage        // int - Quilometragem atual
$status         // enum - active, maintenance, inactive
$driver_id      // int - FK para user (condutor)
$photo          // string - Caminho da foto
$created_at     // timestamp
$updated_at     // timestamp

// Relações
$vehicle->company       // Company
$vehicle->driver        // User
$vehicle->maintenances  // Maintenance[]
$vehicle->fuelLogs      // FuelLog[]
$vehicle->documents     // Document[]
```

### Maintenance (Manutenções)

```php
// Propriedades
$id             // int
$company_id     // int
$vehicle_id     // int - FK para vehicles
$type           // string - Tipo (preventive, corrective, inspection)
$date           // date - Data da manutenção
$cost           // decimal - Custo
$mileage_record // int - Quilometragem no momento
$next_date      // date - Próxima manutenção
$workshop       // string - Oficina
$notes          // text - Observações
$created_at     // timestamp
$updated_at     // timestamp

// Relações
$maintenance->vehicle   // Vehicle
$maintenance->company   // Company

// Métodos úteis
Maintenance::getStatsByCompany($companyId)
Maintenance::getMonthlyCosts($companyId, $months)
Maintenance::getCostsByType($companyId)
Maintenance::getUpcoming($companyId, $days)
```

### FuelLog (Abastecimentos)

```php
// Propriedades
$id             // int
$company_id     // int
$vehicle_id     // int
$date           // date - Data do abastecimento
$liters         // decimal - Litros abastecidos
$price_per_liter // decimal - Preço por litro
$total_cost     // decimal - Custo total
$mileage        // int - Quilometragem
$station        // string - Posto de abastecimento
$fuel_type      // string - Tipo de combustível
$notes          // text
$created_at     // timestamp

// Relações
$fuelLog->vehicle   // Vehicle

// Cálculo automático
public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {
        // Calcular custo total automaticamente
        if ($this->liters && $this->price_per_liter) {
            $this->total_cost = $this->liters * $this->price_per_liter;
        }
        return true;
    }
    return false;
}
```

### Document (Documentos)

```php
// Propriedades
$id             // int
$company_id     // int
$file_id        // int - FK para files
$vehicle_id     // int - FK para vehicles (opcional)
$driver_id      // int - FK para user (opcional)
$type           // enum - registration, insurance, inspection, license, other
$expiry_date    // date - Data de validade
$status         // enum - valid, expired
$notes          // text
$created_at     // timestamp

// Constantes
const TYPE_REGISTRATION = 'registration';
const TYPE_INSURANCE = 'insurance';
const TYPE_INSPECTION = 'inspection';
const TYPE_LICENSE = 'license';
const TYPE_OTHER = 'other';

// Relações
$document->file     // File
$document->vehicle  // Vehicle
$document->driver   // User
```

### Alert (Alertas)

```php
// Propriedades
$id             // int
$company_id     // int
$type           // enum - maintenance, document, fuel, other
$title          // string
$description    // text
$priority       // enum - low, medium, high, critical
$status         // enum - active, resolved, ignored
$details        // json - Dados adicionais
$created_at     // timestamp
$resolved_at    // timestamp

// Constantes
const TYPE_MAINTENANCE = 'maintenance';
const TYPE_DOCUMENT = 'document';
const TYPE_FUEL = 'fuel';
const TYPE_OTHER = 'other';

const PRIORITY_LOW = 'low';
const PRIORITY_MEDIUM = 'medium';
const PRIORITY_HIGH = 'high';
const PRIORITY_CRITICAL = 'critical';

const STATUS_ACTIVE = 'active';
const STATUS_RESOLVED = 'resolved';
const STATUS_IGNORED = 'ignored';
```

### Company (Empresas)

```php
// Propriedades
$id             // int
$code           // string - Código único (VEI001)
$name           // string - Nome da empresa
$email          // string
$phone          // string
$tax_id         // string - NIF
$address        // string
$city           // string
$postal_code    // string
$country        // string
$status         // enum - active, inactive
$created_at     // timestamp

// Relações
$company->vehicles  // Vehicle[]
$company->users     // User[]
```

### Route (Rotas)

```php
// Propriedades
$id             // int
$company_id     // int
$vehicle_id     // int
$driver_id      // int
$start_location // string - Local de partida
$end_location   // string - Local de chegada
$start_time     // datetime
$end_time       // datetime
$status         // enum - planned, in_progress, completed, cancelled
$created_at     // timestamp

// Relações
$route->vehicle     // Vehicle
$route->driver      // User
$route->tickets     // Ticket[]
```

### Ticket (Bilhetes)

```php
// Propriedades
$id             // int
$company_id     // int
$route_id       // int
$passenger_name // string
$passenger_phone // string
$status         // enum - active, cancelled, completed
$created_at     // timestamp

// Relações
$ticket->route      // Route

// Métodos
$ticket->cancel()   // Cancela o bilhete
$ticket->complete() // Marca como completo
```

---

## Validações Comuns

```php
public function rules()
{
    return [
        // Obrigatório
        [['campo'], 'required'],
        
        // Tipo string com tamanho
        [['campo'], 'string', 'max' => 255],
        
        // Inteiro
        [['campo'], 'integer'],
        
        // Decimal
        [['campo'], 'number'],
        
        // Email
        [['email'], 'email'],
        
        // URL
        [['website'], 'url'],
        
        // Data
        [['data'], 'date', 'format' => 'php:Y-m-d'],
        
        // Valores permitidos (enum)
        [['status'], 'in', 'range' => ['active', 'inactive']],
        
        // Único
        [['codigo'], 'unique'],
        
        // FK existe
        [['company_id'], 'exist', 
            'targetClass' => Company::class, 
            'targetAttribute' => 'id'
        ],
        
        // Valor padrão
        [['status'], 'default', 'value' => 'active'],
        
        // Seguro (não validar, apenas permitir atribuição)
        [['campo'], 'safe'],
        
        // Personalizado
        [['campo'], 'validarCampoCustom'],
    ];
}

// Validação personalizada
public function validarCampoCustom($attribute, $params)
{
    if ($this->$attribute < 0) {
        $this->addError($attribute, 'O valor não pode ser negativo');
    }
}
```

## Próximos Passos

- [Autenticação](autenticacao.md)
- [Endpoints Completos](endpoints.md)
