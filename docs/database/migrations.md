# 🔄 Migrations

## Visão Geral

As migrations permitem versionar e aplicar alterações à base de dados de forma controlada. Estão em `console/migrations/`.

## Comandos Básicos

### Aplicar Migrations

```bash
# Aplicar todas as migrations pendentes
php yii migrate

# Aplicar com confirmação automática
php yii migrate --interactive=0

# Aplicar um número específico de migrations
php yii migrate 3
```

### Reverter Migrations

```bash
# Reverter a última migration
php yii migrate/down

# Reverter múltiplas migrations
php yii migrate/down 3

# Reverter todas (cuidado!)
php yii migrate/down all
```

### Criar Migration

```bash
# Criar nova migration
php yii migrate/create nome_da_migration

# Exemplo
php yii migrate/create create_vehicles_table
php yii migrate/create add_status_column_to_maintenance
```

### Outros Comandos

```bash
# Ver histórico de migrations
php yii migrate/history

# Ver migrations pendentes
php yii migrate/new

# Marcar migration como aplicada (sem executar)
php yii migrate/mark m210101_000000_migration_name
```

---

## Estrutura de uma Migration

### Template Básico

```php
<?php
// console/migrations/m240101_120000_create_vehicles_table.php

use yii\db\Migration;

class m240101_120000_create_vehicles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Operações de criação/alteração
        $this->createTable('{{%vehicle}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'license_plate' => $this->string(20)->notNull(),
            'brand' => $this->string(100)->notNull(),
            'model' => $this->string(100)->notNull(),
            'year' => $this->smallInteger(),
            'status' => $this->string(20)->defaultValue('active'),
            'created_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        
        // Índices
        $this->createIndex(
            'idx-vehicle-company_id',
            '{{%vehicle}}',
            'company_id'
        );
        
        // Foreign keys
        $this->addForeignKey(
            'fk-vehicle-company_id',
            '{{%vehicle}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE',  // ON DELETE
            'CASCADE'   // ON UPDATE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Operações de reversão (ordem inversa)
        $this->dropForeignKey('fk-vehicle-company_id', '{{%vehicle}}');
        $this->dropIndex('idx-vehicle-company_id', '{{%vehicle}}');
        $this->dropTable('{{%vehicle}}');
    }
}
```

---

## Exemplos de Migrations

### Criar Tabela

```php
public function safeUp()
{
    $this->createTable('{{%maintenance}}', [
        'id' => $this->primaryKey(),
        'company_id' => $this->integer()->notNull(),
        'vehicle_id' => $this->integer()->notNull(),
        'type' => $this->string(50)->notNull()->defaultValue('preventive'),
        'description' => $this->text()->notNull(),
        'date' => $this->date()->notNull(),
        'cost' => $this->decimal(12, 2)->defaultValue(0),
        'status' => $this->string(20)->defaultValue('scheduled'),
        'created_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
        'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
    ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

public function safeDown()
{
    $this->dropTable('{{%maintenance}}');
}
```

### Adicionar Coluna

```php
public function safeUp()
{
    $this->addColumn('{{%vehicle}}', 'fuel_type', 
        $this->string(20)->defaultValue('diesel')->after('color'));
    
    $this->addColumn('{{%vehicle}}', 'tank_capacity', 
        $this->decimal(10, 2)->after('fuel_type'));
}

public function safeDown()
{
    $this->dropColumn('{{%vehicle}}', 'tank_capacity');
    $this->dropColumn('{{%vehicle}}', 'fuel_type');
}
```

### Alterar Coluna

```php
public function safeUp()
{
    // Alterar tipo
    $this->alterColumn('{{%maintenance}}', 'cost', 
        $this->decimal(15, 2)->notNull()->defaultValue(0));
    
    // Renomear
    $this->renameColumn('{{%vehicle}}', 'plate', 'license_plate');
}

public function safeDown()
{
    $this->renameColumn('{{%vehicle}}', 'license_plate', 'plate');
    $this->alterColumn('{{%maintenance}}', 'cost', 
        $this->decimal(12, 2)->defaultValue(0));
}
```

### Adicionar Índice

```php
public function safeUp()
{
    // Índice simples
    $this->createIndex(
        'idx-vehicle-status',
        '{{%vehicle}}',
        'status'
    );
    
    // Índice composto
    $this->createIndex(
        'idx-maintenance-company-date',
        '{{%maintenance}}',
        ['company_id', 'date']
    );
    
    // Índice único
    $this->createIndex(
        'idx-vehicle-plate-unique',
        '{{%vehicle}}',
        'license_plate',
        true  // unique
    );
}

public function safeDown()
{
    $this->dropIndex('idx-vehicle-plate-unique', '{{%vehicle}}');
    $this->dropIndex('idx-maintenance-company-date', '{{%maintenance}}');
    $this->dropIndex('idx-vehicle-status', '{{%vehicle}}');
}
```

### Foreign Key

```php
public function safeUp()
{
    $this->addForeignKey(
        'fk-maintenance-vehicle',      // Nome da FK
        '{{%maintenance}}',             // Tabela origem
        'vehicle_id',                   // Coluna origem
        '{{%vehicle}}',                 // Tabela destino
        'id',                           // Coluna destino
        'CASCADE',                      // ON DELETE
        'CASCADE'                       // ON UPDATE
    );
}

public function safeDown()
{
    $this->dropForeignKey('fk-maintenance-vehicle', '{{%maintenance}}');
}
```

### Inserir Dados

```php
public function safeUp()
{
    // Insert único
    $this->insert('{{%company}}', [
        'name' => 'Empresa Demo',
        'nif' => '123456789',
        'status' => 'active',
    ]);
    
    // Insert em batch
    $this->batchInsert('{{%alert_type}}', 
        ['code', 'name', 'description'], 
        [
            ['document_expiry', 'Documento a Expirar', 'Alerta de validade de documento'],
            ['maintenance_due', 'Manutenção Pendente', 'Alerta de manutenção programada'],
            ['insurance_expiry', 'Seguro a Expirar', 'Alerta de validade de seguro'],
        ]
    );
}

public function safeDown()
{
    $this->delete('{{%alert_type}}', ['code' => ['document_expiry', 'maintenance_due', 'insurance_expiry']]);
    $this->delete('{{%company}}', ['nif' => '123456789']);
}
```

### SQL Raw

```php
public function safeUp()
{
    // Para operações complexas
    $this->execute('
        ALTER TABLE {{%maintenance}} 
        ADD CONSTRAINT chk_cost_positive 
        CHECK (cost >= 0)
    ');
    
    // Stored Procedure
    $this->execute('
        CREATE PROCEDURE update_vehicle_mileage(
            IN p_vehicle_id INT,
            IN p_mileage INT
        )
        BEGIN
            UPDATE {{%vehicle}} 
            SET current_mileage = p_mileage 
            WHERE id = p_vehicle_id AND current_mileage < p_mileage;
        END
    ');
}

public function safeDown()
{
    $this->execute('DROP PROCEDURE IF EXISTS update_vehicle_mileage');
    // CHECK constraints são removidos automaticamente ao dropar a tabela
}
```

---

## Tipos de Colunas Disponíveis

```php
// Inteiros
$this->primaryKey()           // INT AUTO_INCREMENT PRIMARY KEY
$this->integer()              // INT
$this->bigInteger()           // BIGINT
$this->smallInteger()         // SMALLINT
$this->tinyInteger()          // TINYINT

// Decimais
$this->decimal(10, 2)         // DECIMAL(10,2)
$this->float()                // FLOAT
$this->double()               // DOUBLE
$this->money()                // DECIMAL(19,4)

// Strings
$this->string(255)            // VARCHAR(255)
$this->text()                 // TEXT
$this->char(1)                // CHAR(1)

// Binário
$this->binary()               // BLOB

// Data/Hora
$this->date()                 // DATE
$this->time()                 // TIME
$this->datetime()             // DATETIME
$this->timestamp()            // TIMESTAMP

// Booleano
$this->boolean()              // TINYINT(1)

// JSON (MySQL 5.7+)
$this->json()                 // JSON
```

### Modificadores

```php
$this->string(100)
    ->notNull()
    ->defaultValue('valor')
    ->comment('Descrição da coluna')
    ->after('outra_coluna')
    ->first()
    ->unique();
```

---

## Boas Práticas

### 1. Usar Transações

```php
// safeUp/safeDown usam transações automaticamente
// Se uma operação falhar, todas são revertidas
public function safeUp()
{
    // Operação 1
    // Operação 2 - se falhar, operação 1 é revertida
}
```

### 2. Nomenclatura Consistente

```
m{YYMMDD}_{HHMMSS}_{descricao}.php

Exemplos:
m240115_143000_create_vehicles_table.php
m240115_150000_add_fuel_type_to_vehicles.php
m240116_090000_create_maintenance_table.php
```

### 3. Migrations Pequenas e Atómicas

```php
// ❌ Evitar: Migration muito grande
public function safeUp()
{
    $this->createTable('vehicles', ...);
    $this->createTable('maintenance', ...);
    $this->createTable('fuel_log', ...);
    // ... 10 tabelas
}

// ✅ Preferir: Uma migration por tabela/alteração
// m240115_143000_create_vehicles_table.php
// m240115_143100_create_maintenance_table.php
// m240115_143200_create_fuel_log_table.php
```

### 4. Sempre Implementar safeDown()

```php
// ❌ Evitar
public function safeDown()
{
    return false;  // Não reversível
}

// ✅ Preferir
public function safeDown()
{
    $this->dropTable('{{%vehicle}}');
}
```

### 5. Usar Prefixo de Tabela

```php
// Com prefixo {{%tabela}} - usa o prefixo configurado
$this->createTable('{{%vehicle}}', ...);

// Resultado com tablePrefix = 'veigest_':
// CREATE TABLE `veigest_vehicle` ...
```

---

## Migrations Recentes ⭐

### m251125_000000_add_status_to_maintenances

Adiciona coluna `status` à tabela `maintenances`:

```php
<?php
use yii\db\Migration;

class m251125_000000_add_status_to_maintenances extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%maintenances}}', 'status', 
            $this->string(20)
                ->notNull()
                ->defaultValue('scheduled')
                ->after('cost')
        );

        $this->createIndex(
            'idx-maintenances-status',
            '{{%maintenances}}',
            'status'
        );
    }

    public function safeDown()
    {
        $this->dropIndex('idx-maintenances-status', '{{%maintenances}}');
        $this->dropColumn('{{%maintenances}}', 'status');
    }
}
```

### m251125_010000_create_profile_history_table

Cria tabela para histórico de alterações de perfil (RF-FO-003.5):

```php
<?php
use yii\db\Migration;

class m251125_010000_create_profile_history_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%profile_history}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'change_type' => $this->string(50)->notNull(), // 'update', 'password', 'photo'
            'changes' => $this->text()->null(),            // JSON com alterações
            'ip_address' => $this->string(45)->null(),     // Suporta IPv6
            'user_agent' => $this->string(255)->null(),
            'created_at' => $this->datetime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Índices
        $this->createIndex('idx-profile_history-user_id', '{{%profile_history}}', 'user_id');
        $this->createIndex('idx-profile_history-change_type', '{{%profile_history}}', 'change_type');
        $this->createIndex('idx-profile_history-created_at', '{{%profile_history}}', 'created_at');

        // Foreign key
        $this->addForeignKey(
            'fk-profile_history-user_id',
            '{{%profile_history}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-profile_history-user_id', '{{%profile_history}}');
        $this->dropTable('{{%profile_history}}');
    }
}
```

---

## Debugging

### Ver SQL Gerado

```php
public function safeUp()
{
    $sql = $this->db->getQueryBuilder()->createTable('{{%test}}', [
        'id' => $this->primaryKey(),
    ]);
    echo $sql;  // Ver o SQL que seria executado
}
```

### Logs de Migration

```bash
# Verbose mode
php yii migrate --verbose

# Apenas simular (dry run) - não disponível nativamente
# Mas pode-se usar DB transaction com rollback
```

---

## Próximos Passos

- [Schema](schema.md)
- [Models ActiveRecord](models.md)
