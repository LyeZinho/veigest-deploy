# 🗄️ Schema da Base de Dados

## Visão Geral

O VeiGest utiliza MySQL/MariaDB com o esquema definido em `database.sql` e gerido através de migrations do Yii2.

## Diagrama ER

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   company   │───────│    user     │───────│   vehicle   │
└─────────────┘       └─────────────┘       └─────────────┘
      │                     │                     │
      │                     │                     │
      ▼                     ▼                     ▼
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    alert    │       │    route    │       │ maintenance │
└─────────────┘       └─────────────┘       └─────────────┘
      │                                           │
      │                                           │
      ▼                                           ▼
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   ticket    │       │  document   │       │  fuel_log   │
└─────────────┘       └─────────────┘       └─────────────┘
                            │
                            ▼
                      ┌─────────────┐
                      │    file     │
                      └─────────────┘
```

---

## Tabelas

### `company` - Empresas

Multi-tenancy: todas as outras tabelas referenciam `company_id`.

```sql
CREATE TABLE `company` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL COMMENT 'Nome da empresa',
    `nif` VARCHAR(20) NULL COMMENT 'NIF/NIPC',
    `address` TEXT NULL COMMENT 'Morada completa',
    `phone` VARCHAR(20) NULL COMMENT 'Telefone',
    `email` VARCHAR(255) NULL COMMENT 'Email de contacto',
    `logo` VARCHAR(255) NULL COMMENT 'Path do logo',
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `subscription_plan` VARCHAR(50) DEFAULT 'basic' COMMENT 'Plano: basic, pro, enterprise',
    `subscription_expires_at` DATETIME NULL COMMENT 'Expiração da subscrição',
    `settings` JSON NULL COMMENT 'Configurações adicionais em JSON',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_company_nif` (`nif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Índices:**
- `PRIMARY KEY (id)`
- `UNIQUE KEY idx_company_nif (nif)`

---

### `user` - Utilizadores

```sql
CREATE TABLE `user` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NULL COMMENT 'FK para company (NULL para super admin)',
    `username` VARCHAR(255) NOT NULL COMMENT 'Nome de utilizador único',
    `auth_key` VARCHAR(32) NOT NULL COMMENT 'Chave de autenticação',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Hash da password',
    `password_reset_token` VARCHAR(255) NULL COMMENT 'Token de reset',
    `email` VARCHAR(255) NOT NULL COMMENT 'Email único',
    `role` ENUM('admin', 'gestor', 'condutor') DEFAULT 'condutor',
    `status` SMALLINT(6) DEFAULT 10 COMMENT '0=eliminado, 9=inactivo, 10=activo',
    `name` VARCHAR(255) NULL COMMENT 'Nome completo',
    `phone` VARCHAR(20) NULL COMMENT 'Telefone',
    `license_number` VARCHAR(50) NULL COMMENT 'Nº carta de condução',
    `license_expiry` DATE NULL COMMENT 'Validade da carta',
    `profile_image` VARCHAR(255) NULL COMMENT 'Foto de perfil',
    `last_login_at` DATETIME NULL COMMENT 'Último login',
    `created_at` INT(11) NOT NULL COMMENT 'Unix timestamp criação',
    `updated_at` INT(11) NOT NULL COMMENT 'Unix timestamp actualização',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_username` (`username`),
    UNIQUE KEY `idx_user_email` (`email`),
    UNIQUE KEY `idx_user_password_reset_token` (`password_reset_token`),
    KEY `idx_user_company` (`company_id`),
    CONSTRAINT `fk_user_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Roles:**
- `admin` - Administrador da empresa
- `gestor` - Gestor de frota
- `condutor` - Condutor

**Status:**
- `0` - Eliminado (soft delete)
- `9` - Inactivo
- `10` - Activo

---

### `vehicle` - Veículos

```sql
CREATE TABLE `vehicle` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL COMMENT 'FK para company',
    `license_plate` VARCHAR(20) NOT NULL COMMENT 'Matrícula',
    `brand` VARCHAR(100) NOT NULL COMMENT 'Marca',
    `model` VARCHAR(100) NOT NULL COMMENT 'Modelo',
    `year` SMALLINT(4) NULL COMMENT 'Ano de fabrico',
    `color` VARCHAR(50) NULL COMMENT 'Cor',
    `vin` VARCHAR(50) NULL COMMENT 'Número de chassis (VIN)',
    `fuel_type` ENUM('gasoline', 'diesel', 'electric', 'hybrid', 'lpg') DEFAULT 'diesel',
    `tank_capacity` DECIMAL(10,2) NULL COMMENT 'Capacidade do depósito (litros)',
    `current_mileage` INT(11) DEFAULT 0 COMMENT 'Quilometragem actual',
    `status` ENUM('active', 'maintenance', 'inactive', 'sold') DEFAULT 'active',
    `assigned_driver_id` INT(11) NULL COMMENT 'Condutor atribuído',
    `insurance_expiry` DATE NULL COMMENT 'Validade do seguro',
    `inspection_expiry` DATE NULL COMMENT 'Validade da inspeção',
    `purchase_date` DATE NULL COMMENT 'Data de compra',
    `purchase_price` DECIMAL(12,2) NULL COMMENT 'Preço de compra',
    `notes` TEXT NULL COMMENT 'Observações',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_vehicle_plate_company` (`license_plate`, `company_id`),
    KEY `idx_vehicle_company` (`company_id`),
    KEY `idx_vehicle_driver` (`assigned_driver_id`),
    KEY `idx_vehicle_status` (`status`),
    CONSTRAINT `fk_vehicle_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_vehicle_driver` FOREIGN KEY (`assigned_driver_id`) 
        REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `maintenance` - Manutenções

```sql
CREATE TABLE `maintenance` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL,
    `vehicle_id` INT(11) NOT NULL COMMENT 'FK para vehicle',
    `type` ENUM('preventive', 'corrective', 'inspection', 'tires', 'oil_change', 'other') 
           DEFAULT 'preventive' COMMENT 'Tipo de manutenção',
    `description` TEXT NOT NULL COMMENT 'Descrição do serviço',
    `date` DATE NOT NULL COMMENT 'Data da manutenção',
    `mileage_at_service` INT(11) NULL COMMENT 'Quilometragem no serviço',
    `cost` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Custo total',
    `workshop` VARCHAR(255) NULL COMMENT 'Oficina/Fornecedor',
    `invoice_number` VARCHAR(100) NULL COMMENT 'Nº da factura',
    `next_service_date` DATE NULL COMMENT 'Próxima manutenção',
    `next_service_mileage` INT(11) NULL COMMENT 'Quilometragem para próxima',
    `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    `notes` TEXT NULL,
    `created_by` INT(11) NULL COMMENT 'Utilizador que criou',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_maintenance_company` (`company_id`),
    KEY `idx_maintenance_vehicle` (`vehicle_id`),
    KEY `idx_maintenance_date` (`date`),
    KEY `idx_maintenance_type` (`type`),
    CONSTRAINT `fk_maintenance_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_maintenance_vehicle` FOREIGN KEY (`vehicle_id`) 
        REFERENCES `vehicle` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_maintenance_user` FOREIGN KEY (`created_by`) 
        REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `fuel_log` - Abastecimentos

```sql
CREATE TABLE `fuel_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL,
    `vehicle_id` INT(11) NOT NULL,
    `driver_id` INT(11) NULL COMMENT 'Condutor que abasteceu',
    `date` DATE NOT NULL COMMENT 'Data do abastecimento',
    `liters` DECIMAL(10,2) NOT NULL COMMENT 'Litros abastecidos',
    `price_per_liter` DECIMAL(10,3) NOT NULL COMMENT 'Preço por litro',
    `total_cost` DECIMAL(12,2) NOT NULL COMMENT 'Custo total',
    `mileage` INT(11) NOT NULL COMMENT 'Quilometragem actual',
    `fuel_type` ENUM('gasoline', 'diesel', 'electric', 'lpg') DEFAULT 'diesel',
    `full_tank` TINYINT(1) DEFAULT 1 COMMENT 'Depósito cheio?',
    `station` VARCHAR(255) NULL COMMENT 'Posto de combustível',
    `receipt_number` VARCHAR(100) NULL COMMENT 'Nº do recibo',
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_fuellog_company` (`company_id`),
    KEY `idx_fuellog_vehicle` (`vehicle_id`),
    KEY `idx_fuellog_driver` (`driver_id`),
    KEY `idx_fuellog_date` (`date`),
    CONSTRAINT `fk_fuellog_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fuellog_vehicle` FOREIGN KEY (`vehicle_id`) 
        REFERENCES `vehicle` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fuellog_driver` FOREIGN KEY (`driver_id`) 
        REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `document` - Documentos

```sql
CREATE TABLE `document` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL,
    `vehicle_id` INT(11) NULL COMMENT 'FK para vehicle (opcional)',
    `driver_id` INT(11) NULL COMMENT 'FK para user/condutor (opcional)',
    `type` ENUM('license', 'insurance', 'inspection', 'registration', 'contract', 'other') 
           NOT NULL COMMENT 'Tipo de documento',
    `name` VARCHAR(255) NOT NULL COMMENT 'Nome do documento',
    `description` TEXT NULL,
    `issue_date` DATE NULL COMMENT 'Data de emissão',
    `expiry_date` DATE NULL COMMENT 'Data de validade',
    `status` ENUM('valid', 'expired', 'expiring_soon', 'cancelled') DEFAULT 'valid',
    `file_id` INT(11) NULL COMMENT 'FK para file',
    `alert_days_before` INT(11) DEFAULT 30 COMMENT 'Dias antes para alertar',
    `notes` TEXT NULL,
    `created_by` INT(11) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_document_company` (`company_id`),
    KEY `idx_document_vehicle` (`vehicle_id`),
    KEY `idx_document_driver` (`driver_id`),
    KEY `idx_document_expiry` (`expiry_date`),
    KEY `idx_document_status` (`status`),
    CONSTRAINT `fk_document_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_document_vehicle` FOREIGN KEY (`vehicle_id`) 
        REFERENCES `vehicle` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_document_driver` FOREIGN KEY (`driver_id`) 
        REFERENCES `user` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_document_file` FOREIGN KEY (`file_id`) 
        REFERENCES `file` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `file` - Ficheiros

```sql
CREATE TABLE `file` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL COMMENT 'Nome original do ficheiro',
    `stored_name` VARCHAR(255) NOT NULL COMMENT 'Nome armazenado (único)',
    `path` VARCHAR(500) NOT NULL COMMENT 'Caminho completo',
    `mime_type` VARCHAR(100) NULL COMMENT 'Tipo MIME',
    `size` INT(11) NOT NULL COMMENT 'Tamanho em bytes',
    `hash` VARCHAR(64) NULL COMMENT 'Hash SHA-256 para deduplicação',
    `uploaded_by` INT(11) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_file_company` (`company_id`),
    KEY `idx_file_hash` (`hash`),
    CONSTRAINT `fk_file_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_file_user` FOREIGN KEY (`uploaded_by`) 
        REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `alert` - Alertas

```sql
CREATE TABLE `alert` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL,
    `vehicle_id` INT(11) NULL COMMENT 'Veículo relacionado',
    `driver_id` INT(11) NULL COMMENT 'Condutor relacionado',
    `document_id` INT(11) NULL COMMENT 'Documento relacionado',
    `maintenance_id` INT(11) NULL COMMENT 'Manutenção relacionada',
    `type` ENUM('document_expiry', 'maintenance_due', 'license_expiry', 'insurance_expiry', 
                'inspection_expiry', 'mileage_threshold', 'custom') NOT NULL,
    `title` VARCHAR(255) NOT NULL COMMENT 'Título do alerta',
    `message` TEXT NULL COMMENT 'Mensagem detalhada',
    `priority` ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    `status` ENUM('active', 'acknowledged', 'resolved', 'dismissed') DEFAULT 'active',
    `due_date` DATE NULL COMMENT 'Data limite',
    `acknowledged_by` INT(11) NULL,
    `acknowledged_at` DATETIME NULL,
    `resolved_by` INT(11) NULL,
    `resolved_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_alert_company` (`company_id`),
    KEY `idx_alert_status` (`status`),
    KEY `idx_alert_priority` (`priority`),
    KEY `idx_alert_due_date` (`due_date`),
    CONSTRAINT `fk_alert_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `route` - Rotas/Viagens

```sql
CREATE TABLE `route` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL,
    `vehicle_id` INT(11) NOT NULL,
    `driver_id` INT(11) NOT NULL,
    `start_location` VARCHAR(255) NOT NULL COMMENT 'Local de partida',
    `end_location` VARCHAR(255) NOT NULL COMMENT 'Local de chegada',
    `start_datetime` DATETIME NOT NULL COMMENT 'Data/hora de partida',
    `end_datetime` DATETIME NULL COMMENT 'Data/hora de chegada',
    `start_mileage` INT(11) NOT NULL COMMENT 'Quilometragem inicial',
    `end_mileage` INT(11) NULL COMMENT 'Quilometragem final',
    `distance` INT(11) NULL COMMENT 'Distância percorrida (km)',
    `purpose` VARCHAR(255) NULL COMMENT 'Motivo da viagem',
    `status` ENUM('planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'planned',
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_route_company` (`company_id`),
    KEY `idx_route_vehicle` (`vehicle_id`),
    KEY `idx_route_driver` (`driver_id`),
    KEY `idx_route_date` (`start_datetime`),
    CONSTRAINT `fk_route_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_route_vehicle` FOREIGN KEY (`vehicle_id`) 
        REFERENCES `vehicle` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_route_driver` FOREIGN KEY (`driver_id`) 
        REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### `ticket` - Tickets de Suporte

```sql
CREATE TABLE `ticket` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `company_id` INT(11) NOT NULL,
    `user_id` INT(11) NOT NULL COMMENT 'Utilizador que criou',
    `vehicle_id` INT(11) NULL COMMENT 'Veículo relacionado (opcional)',
    `subject` VARCHAR(255) NOT NULL COMMENT 'Assunto',
    `description` TEXT NOT NULL COMMENT 'Descrição detalhada',
    `category` ENUM('technical', 'billing', 'feature_request', 'bug_report', 'other') 
               DEFAULT 'technical',
    `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    `status` ENUM('open', 'in_progress', 'waiting_response', 'resolved', 'closed') 
             DEFAULT 'open',
    `assigned_to` INT(11) NULL COMMENT 'Utilizador atribuído',
    `resolved_at` DATETIME NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ticket_company` (`company_id`),
    KEY `idx_ticket_user` (`user_id`),
    KEY `idx_ticket_status` (`status`),
    CONSTRAINT `fk_ticket_company` FOREIGN KEY (`company_id`) 
        REFERENCES `company` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) 
        REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Índices e Performance

### Índices Recomendados

```sql
-- Queries frequentes por empresa + data
CREATE INDEX idx_maintenance_company_date ON maintenance(company_id, date DESC);
CREATE INDEX idx_fuellog_company_date ON fuel_log(company_id, date DESC);
CREATE INDEX idx_route_company_date ON route(company_id, start_datetime DESC);

-- Alertas activos por empresa
CREATE INDEX idx_alert_active ON alert(company_id, status, priority);

-- Documentos a expirar
CREATE INDEX idx_document_expiring ON document(company_id, status, expiry_date);

-- Pesquisa por matrícula
CREATE INDEX idx_vehicle_plate ON vehicle(license_plate);
```

---

## Próximos Passos

- [Migrations](migrations.md)
- [Models ActiveRecord](models.md)
