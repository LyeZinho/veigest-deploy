<?php

use yii\db\Migration;

/**
 * VeiGest - Ultra-Simplified Schema with RBAC Yii2
 * Last update: 2025-11-22 (Revision 5 - English standardization)
 * ULTRA-LEAN: 13 main tables + 4 RBAC + 3 views
 * Removed: GPS tracking system
 * Changes: Removed deprecated 'estado' field, standardized roles to English (manager, driver)
 */
class m251121_000000_veigest_consolidated_migration extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');

        // ============================================================================
        // 1. COMPANIES
        // ============================================================================
        $this->createTable('{{%companies}}', [
            'id' => $this->primaryKey(),
            //'code' => $this->string(50)->unique()->comment('Unique company code'), // Code deve ser gerado atuomaticamente durante a criação
            // Código único da empresa: agora definido como um número inteiro grande (UID numérico randômico)
            'code' => $this->bigInteger()->notNull()->comment('Unique company numeric code'),
            'name' => $this->string(200)->notNull(),
            'tax_id' => $this->string(20)->notNull(),
            'email' => $this->string(150),
            'phone' => $this->string(20),
            'status' => "ENUM('active','suspended','inactive') NOT NULL DEFAULT 'active'",
            'plan' => "ENUM('basic','professional','enterprise') NOT NULL DEFAULT 'basic'",
            'settings' => $this->json()->comment('Company-specific settings'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_tax_id', '{{%companies}}', 'tax_id', true);
        $this->createIndex('idx_status', '{{%companies}}', 'status');
        // Indice único para o código numérico da empresa
        $this->createIndex('idx_code', '{{%companies}}', 'code', true);

        // ============================================================================
        // 2. RBAC Yii2 (Access Control System)
        // ============================================================================
        $this->createTable('{{%auth_rule}}', [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_rule', '{{%auth_rule}}', 'name');

        $this->createTable('{{%auth_item}}', [
            'name' => $this->string(64)->notNull(),
            'type' => $this->tinyInteger()->notNull()->comment('1=role, 2=permission'),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_item', '{{%auth_item}}', 'name');
        $this->createIndex('idx_type', '{{%auth_item}}', 'type');

        $this->createTable('{{%auth_item_child}}', [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_item_child', '{{%auth_item_child}}', ['parent', 'child']);

        $this->createTable('{{%auth_assignment}}', [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        $this->addPrimaryKey('pk_auth_assignment', '{{%auth_assignment}}', ['item_name', 'user_id']);
        $this->createIndex('idx_user_id', '{{%auth_assignment}}', 'user_id');

        // Foreign keys for RBAC
        $this->addForeignKey('fk_auth_item_rule', '{{%auth_item}}', 'rule_name', '{{%auth_rule}}', 'name', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_auth_item_child_parent', '{{%auth_item_child}}', 'parent', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_auth_item_child_child', '{{%auth_item_child}}', 'child', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_auth_assignment_item', '{{%auth_assignment}}', 'item_name', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');

        // ============================================================================
        // 3. USERS (with integrated driver profile)
        // ============================================================================
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'name' => $this->string(150)->notNull(),
            'username' => $this->string(100)->notNull(),
            'email' => $this->string(150)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'phone' => $this->string(20),
            'status' => "ENUM('active','inactive') NOT NULL DEFAULT 'active'",
            'auth_key' => $this->string(32)->comment('For Yii2 authentication'),
            'password_reset_token' => $this->string(255),
            'verification_token' => $this->string(255)->comment('For email verification'),
            // Driver fields (only filled if user is a driver)
            'license_number' => $this->string(50),
            'license_expiry' => $this->date(),
            'photo' => $this->string(255),
            // RBAC integration field
            'roles' => $this->string(255)->comment('Comma-separated list of user roles for quick access'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('uk_email_company', '{{%users}}', ['email', 'company_id'], true);
        $this->createIndex('idx_company_id', '{{%users}}', 'company_id');
        $this->createIndex('idx_status', '{{%users}}', 'status');
        $this->createIndex('idx_license_expiry', '{{%users}}', 'license_expiry');
        $this->createIndex('idx_password_reset_token', '{{%users}}', 'password_reset_token');
        $this->createIndex('idx_verification_token', '{{%users}}', 'verification_token');
        $this->createIndex('idx_roles', '{{%users}}', 'roles');
        $this->addForeignKey('fk_users_company', '{{%users}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');

        // ============================================================================
        // 3. FILES
        // ============================================================================
        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'original_name' => $this->string(255)->notNull(),
            'size' => $this->bigInteger()->notNull(),
            'path' => $this->string(500)->notNull()->comment('Full file path'),
            'uploaded_by' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_files_company_id', '{{%files}}', 'company_id');
        $this->createIndex('idx_files_uploaded_by', '{{%files}}', 'uploaded_by');
        $this->addForeignKey('fk_files_company', '{{%files}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_files_uploaded_by', '{{%files}}', 'uploaded_by', '{{%users}}', 'id', 'RESTRICT');

        // ============================================================================
        // 4. VEHICLES
        // ============================================================================
        $this->createTable('{{%vehicles}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'license_plate' => $this->string(20)->notNull(),
            'brand' => $this->string(100),
            'model' => $this->string(100),
            'year' => $this->integer(),
            'fuel_type' => "ENUM('gasoline','diesel','electric','hybrid','other')",
            'mileage' => $this->integer()->notNull()->defaultValue(0),
            'status' => "ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active'",
            'driver_id' => $this->integer()->comment('Currently assigned driver'),
            'photo' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('uk_license_plate_company', '{{%vehicles}}', ['license_plate', 'company_id'], true);
        $this->createIndex('idx_vehicles_company_id', '{{%vehicles}}', 'company_id');
        $this->createIndex('idx_vehicles_status', '{{%vehicles}}', 'status');
        $this->createIndex('idx_vehicles_driver_id', '{{%vehicles}}', 'driver_id');
        $this->addForeignKey('fk_vehicles_company', '{{%vehicles}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_vehicles_driver', '{{%vehicles}}', 'driver_id', '{{%users}}', 'id', 'SET NULL');

        // ============================================================================
        // 5. MAINTENANCES
        // ============================================================================
        $this->createTable('{{%maintenances}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer()->notNull(),
            'type' => $this->string(100)->notNull(),
            'description' => $this->text(),
            'date' => $this->date()->notNull(),
            'cost' => $this->decimal(10, 2)->defaultValue(0.00),
            'mileage_record' => $this->integer(),
            'next_date' => $this->date(),
            'workshop' => $this->string(200),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_maintenances_vehicle_id', '{{%maintenances}}', 'vehicle_id');
        $this->createIndex('idx_maintenances_date', '{{%maintenances}}', 'date');
        $this->createIndex('idx_maintenances_next_date', '{{%maintenances}}', 'next_date');
        $this->addForeignKey('fk_maintenances_company', '{{%maintenances}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_maintenances_vehicle', '{{%maintenances}}', 'vehicle_id', '{{%vehicles}}', 'id', 'CASCADE');

        // ============================================================================
        // 6. DOCUMENTS
        // ============================================================================
        $this->createTable('{{%documents}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'file_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer(),
            'driver_id' => $this->integer(),
            'type' => "ENUM('registration','insurance','inspection','license','other') NOT NULL",
            'expiry_date' => $this->date(),
            'status' => "ENUM('valid','expired') NOT NULL DEFAULT 'valid'",
            'notes' => $this->text()->comment('Additional document information'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_documents_file_id', '{{%documents}}', 'file_id');
        $this->createIndex('idx_documents_vehicle_id', '{{%documents}}', 'vehicle_id');
        $this->createIndex('idx_documents_driver_id', '{{%documents}}', 'driver_id');
        $this->createIndex('idx_documents_expiry_date', '{{%documents}}', 'expiry_date');
        $this->addForeignKey('fk_documents_company', '{{%documents}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_documents_file', '{{%documents}}', 'file_id', '{{%files}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_documents_vehicle', '{{%documents}}', 'vehicle_id', '{{%vehicles}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_documents_driver', '{{%documents}}', 'driver_id', '{{%users}}', 'id', 'CASCADE');

        // ============================================================================
        // 7. FUEL LOGS
        // ============================================================================
        $this->createTable('{{%fuel_logs}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer()->notNull(),
            'driver_id' => $this->integer(),
            'date' => $this->date()->notNull(),
            'liters' => $this->decimal(10, 2)->notNull(),
            'value' => $this->decimal(10, 2)->notNull(),
            'price_per_liter' => $this->decimal(8, 4),
            'current_mileage' => $this->integer(),
            'notes' => $this->string(255),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_fuel_logs_vehicle_id', '{{%fuel_logs}}', 'vehicle_id');
        $this->createIndex('idx_fuel_logs_date', '{{%fuel_logs}}', 'date');
        $this->addForeignKey('fk_fuel_logs_company', '{{%fuel_logs}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_fuel_logs_vehicle', '{{%fuel_logs}}', 'vehicle_id', '{{%vehicles}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_fuel_logs_driver', '{{%fuel_logs}}', 'driver_id', '{{%users}}', 'id', 'SET NULL');

        // ============================================================================
        // 8. ALERTS
        // ============================================================================
        $this->createTable('{{%alerts}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'type' => "ENUM('maintenance','document','fuel','other') NOT NULL",
            'title' => $this->string(200)->notNull(),
            'description' => $this->text(),
            'priority' => "ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium'",
            'status' => "ENUM('active','resolved','ignored') NOT NULL DEFAULT 'active'",
            'details' => $this->json()->comment('vehicle_id, document_id, user_id, etc.'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'resolved_at' => $this->dateTime(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_alerts_status', '{{%alerts}}', 'status');
        $this->createIndex('idx_alerts_company_id', '{{%alerts}}', 'company_id');
        $this->createIndex('idx_alerts_type', '{{%alerts}}', 'type');
        $this->addForeignKey('fk_alerts_company', '{{%alerts}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');

        // ============================================================================
        // 9. ACTIVITY LOGS
        // ============================================================================
        $this->createTable('{{%activity_logs}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'action' => $this->string(255)->notNull(),
            'entity' => $this->string(100)->notNull()->comment('Ex: vehicle, document, user'),
            'entity_id' => $this->integer(),
            'details' => $this->json(),
            'ip' => $this->string(45),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_activity_logs_created_at', '{{%activity_logs}}', 'created_at');
        $this->createIndex('idx_activity_logs_entity', '{{%activity_logs}}', ['entity', 'entity_id']);
        $this->createIndex('idx_activity_logs_user_id', '{{%activity_logs}}', 'user_id');
        $this->addForeignKey('fk_activity_logs_company', '{{%activity_logs}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_activity_logs_user', '{{%activity_logs}}', 'user_id', '{{%users}}', 'id', 'SET NULL');

        // ============================================================================
        // 10. ROUTES
        // ============================================================================
        $this->createTable('{{%routes}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'vehicle_id' => $this->integer()->notNull(),
            'driver_id' => $this->integer()->notNull(),
            'start_location' => $this->string(255)->notNull(),
            'end_location' => $this->string(255)->notNull(),
            'start_time' => $this->dateTime()->notNull(),
            'end_time' => $this->dateTime(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_routes_company_id', '{{%routes}}', 'company_id');
        $this->createIndex('idx_routes_vehicle_id', '{{%routes}}', 'vehicle_id');
        $this->createIndex('idx_routes_driver_id', '{{%routes}}', 'driver_id');
        $this->createIndex('idx_routes_start_time', '{{%routes}}', 'start_time');
        $this->addForeignKey('fk_routes_company', '{{%routes}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_routes_vehicle', '{{%routes}}', 'vehicle_id', '{{%vehicles}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_routes_driver', '{{%routes}}', 'driver_id', '{{%users}}', 'id', 'RESTRICT');

        // ============================================================================
        // 11. SUPPORT TICKETS
        // ============================================================================
        $this->createTable('{{%tickets}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->null()->comment('NULL for guest tickets'),
            'name' => $this->string(150)->notNull()->comment('Ticket creator name'),
            'email' => $this->string(150)->notNull()->comment('Ticket creator email'),
            'subject' => $this->string(255)->notNull()->comment('Ticket subject'),
            'body' => $this->text()->notNull()->comment('Ticket description'),
            'priority' => "ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium'",
            'category' => "ENUM('technical','billing','account','feature','training','other') NOT NULL DEFAULT 'other'",
            'status' => "ENUM('open','in_progress','waiting_response','resolved','closed') NOT NULL DEFAULT 'open'",
            'admin_response' => $this->text()->null()->comment('Admin/Manager response'),
            'responded_by' => $this->integer()->null()->comment('Admin/Manager who responded'),
            'responded_at' => $this->dateTime()->null()->comment('Response timestamp'),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->createIndex('idx_tickets_company_id', '{{%tickets}}', 'company_id');
        $this->createIndex('idx_tickets_user_id', '{{%tickets}}', 'user_id');
        $this->createIndex('idx_tickets_status', '{{%tickets}}', 'status');
        $this->createIndex('idx_tickets_priority', '{{%tickets}}', 'priority');
        $this->createIndex('idx_tickets_category', '{{%tickets}}', 'category');
        $this->createIndex('idx_tickets_created_at', '{{%tickets}}', 'created_at');
        $this->addForeignKey('fk_tickets_company', '{{%tickets}}', 'company_id', '{{%companies}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_tickets_user', '{{%tickets}}', 'user_id', '{{%users}}', 'id', 'SET NULL');
        $this->addForeignKey('fk_tickets_responded_by', '{{%tickets}}', 'responded_by', '{{%users}}', 'id', 'SET NULL');

        // ============================================================================
        // VIEWS
        // ============================================================================
        $this->execute("
            CREATE VIEW v_documents_expiring AS
            SELECT
                d.id,
                d.company_id,
                d.type,
                d.expiry_date,
                d.status,
                f.original_name,
                DATEDIFF(d.expiry_date, CURDATE()) AS days_to_expiry,
                COALESCE(v.license_plate, CONCAT('Driver: ', u.name)) AS entity
            FROM documents d
            INNER JOIN files f ON d.file_id = f.id
            LEFT JOIN vehicles v ON d.vehicle_id = v.id
            LEFT JOIN users u ON d.driver_id = u.id
            WHERE d.expiry_date IS NOT NULL
              AND d.status = 'valid'
              AND DATEDIFF(d.expiry_date, CURDATE()) <= 30
            ORDER BY d.expiry_date ASC
        ");

        $this->execute("
            CREATE VIEW v_company_stats AS
            SELECT
                c.id,
                c.name,
                c.plan,
                c.status,
                COUNT(DISTINCT u.id) AS total_users,
                COUNT(DISTINCT v.id) AS total_vehicles,
                COUNT(DISTINCT CASE WHEN u.license_number IS NOT NULL THEN u.id END) AS total_drivers,
                COALESCE(SUM(f.size), 0) AS total_storage_bytes
            FROM companies c
            LEFT JOIN users u ON c.id = u.company_id AND u.status = 'active'
            LEFT JOIN vehicles v ON c.id = v.company_id AND v.status != 'inactive'
            LEFT JOIN files f ON c.id = f.company_id
            GROUP BY c.id
        ");

        $this->execute("
            CREATE VIEW v_vehicle_costs AS
            SELECT
                v.id AS vehicle_id,
                v.company_id,
                v.license_plate,
                v.brand,
                v.model,
                COALESCE(SUM(m.cost), 0) AS total_maintenance,
                COALESCE(SUM(fl.value), 0) AS total_fuel,
                COALESCE(SUM(m.cost), 0) + COALESCE(SUM(fl.value), 0) AS total_costs
            FROM vehicles v
            LEFT JOIN maintenances m ON v.id = m.vehicle_id
            LEFT JOIN fuel_logs fl ON v.id = fl.vehicle_id
            GROUP BY v.id
        ");

        // ============================================================================
        // INITIAL DATA
        // ============================================================================

        // Default company
        // Gera um código único numérico (bigint) para a empresa demo
        $demoCompanyCode = (int) (microtime(true) * 1000) + random_int(1000, 9999);
        $this->insert('{{%companies}}', [
            'id' => 1,
            'code' => $demoCompanyCode,
            'name' => 'VeiGest - Demo Company',
            'tax_id' => '999999990',
            'email' => 'admin@veigest.com',
            'status' => 'active',
            'plan' => 'enterprise',
            'settings' => json_encode([
                'currency' => 'EUR',
                'timezone' => 'Europe/Lisbon',
                'language' => 'en',
                'email_alerts' => true,
                'days_alert_documents' => 30
            ])
        ]);

        // Admin user
        $this->insert('{{%users}}', [
            'id' => 1,
            'company_id' => 1,
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@veigest.com',
            'password_hash' => '$2a$12$yjs8TTsveJPiMeAg1D5fNePfO9rPOKmRRDBnW1xUFfss/NEPZhvEa', // password: admin
            'status' => 'active',
            'auth_key' => md5('admin@veigest.com' . time()),
            'roles' => 'admin',
        ]);

        // ============================================================================
        // RBAC: ROLES
        // ============================================================================
        $time = time();
        $this->batchInsert('{{%auth_item}}', 
            ['name', 'type', 'description', 'created_at', 'updated_at'],
            [
                ['admin', 1, 'Administrator', $time, $time],
                ['manager', 1, 'Manager', $time, $time],
                ['driver', 1, 'Driver', $time, $time],
            ]
        );

        // ============================================================================
        // RBAC: PERMISSIONS
        // ============================================================================
        $permissions = [
            // Companies
            ['companies.view', 2, 'View companies', $time, $time],
            ['companies.manage', 2, 'Manage companies', $time, $time],
            
            // Users
            ['users.view', 2, 'View users', $time, $time],
            ['users.create', 2, 'Create users', $time, $time],
            ['users.update', 2, 'Edit users', $time, $time],
            ['users.delete', 2, 'Delete users', $time, $time],
            ['users.manage-roles', 2, 'Manage user roles', $time, $time],
            
            // Vehicles
            ['vehicles.view', 2, 'View vehicles', $time, $time],
            ['vehicles.create', 2, 'Create vehicles', $time, $time],
            ['vehicles.update', 2, 'Edit vehicles', $time, $time],
            ['vehicles.delete', 2, 'Delete vehicles', $time, $time],
            ['vehicles.assign', 2, 'Assign vehicles to drivers', $time, $time],
            
            // Drivers
            ['drivers.view', 2, 'View driver profiles', $time, $time],
            ['drivers.create', 2, 'Create driver profiles', $time, $time],
            ['drivers.update', 2, 'Edit driver profiles', $time, $time],
            ['drivers.delete', 2, 'Delete driver profiles', $time, $time],
            
            // Files
            ['files.view', 2, 'View files', $time, $time],
            ['files.upload', 2, 'Upload files', $time, $time],
            ['files.delete', 2, 'Delete files', $time, $time],
            
            // Maintenances
            ['maintenances.view', 2, 'View maintenances', $time, $time],
            ['maintenances.create', 2, 'Create maintenances', $time, $time],
            ['maintenances.update', 2, 'Edit maintenances', $time, $time],
            ['maintenances.delete', 2, 'Delete maintenances', $time, $time],
            ['maintenances.schedule', 2, 'Schedule maintenances', $time, $time],
            
            // Documents
            ['documents.view', 2, 'View documents', $time, $time],
            ['documents.create', 2, 'Create documents', $time, $time],
            ['documents.update', 2, 'Edit documents', $time, $time],
            ['documents.delete', 2, 'Delete documents', $time, $time],
            
            // Fuel
            ['fuel.view', 2, 'View fuel logs', $time, $time],
            ['fuel.create', 2, 'Record fuel', $time, $time],
            ['fuel.update', 2, 'Edit fuel logs', $time, $time],
            ['fuel.delete', 2, 'Delete fuel logs', $time, $time],
            
            // Alerts
            ['alerts.view', 2, 'View alerts', $time, $time],
            ['alerts.create', 2, 'Create alerts', $time, $time],
            ['alerts.resolve', 2, 'Resolve alerts', $time, $time],
            
            // Reports
            ['reports.view', 2, 'View reports', $time, $time],
            ['reports.create', 2, 'Generate reports', $time, $time],
            ['reports.export', 2, 'Export reports', $time, $time],
            ['reports.advanced', 2, 'Advanced reports', $time, $time],
            
            // System
            ['system.config', 2, 'System settings', $time, $time],
            ['system.logs', 2, 'View system logs', $time, $time],
            
            // Dashboard
            ['dashboard.view', 2, 'View dashboard', $time, $time],
            ['dashboard.advanced', 2, 'Advanced dashboard', $time, $time],
            
            // Routes
            ['routes.view', 2, 'View routes', $time, $time],
            ['routes.create', 2, 'Create routes', $time, $time],
            ['routes.update', 2, 'Edit routes', $time, $time],
            ['routes.delete', 2, 'Delete routes', $time, $time],
            
            // Support Tickets
            ['tickets.view', 2, 'View support tickets', $time, $time],
            ['tickets.create', 2, 'Create support tickets (any logged user)', $time, $time],
            ['tickets.update', 2, 'Update/respond to support tickets', $time, $time],
            ['tickets.delete', 2, 'Delete support tickets', $time, $time],
            ['tickets.respond', 2, 'Respond to support tickets', $time, $time],
        ];

        $this->batchInsert('{{%auth_item}}', 
            ['name', 'type', 'description', 'created_at', 'updated_at'],
            $permissions
        );

        // ============================================================================
        // RBAC: LINK PERMISSIONS TO ROLES
        // ============================================================================

        // Super Admin: All permissions
        // Removed super-admin role - no top-level privilege role is created now

        // Admin: All except critical settings
        $this->execute("INSERT INTO auth_item_child (parent, child) SELECT 'admin', name FROM auth_item WHERE type = 2 AND name NOT IN ('system.config')");

        // Manager (Fleet Administrator) - Full access to frontend operations
        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], [
            ['manager', 'companies.view'],
            ['manager', 'users.view'], ['manager', 'users.create'], ['manager', 'users.update'],
            ['manager', 'vehicles.view'], ['manager', 'vehicles.create'], ['manager', 'vehicles.update'], ['manager', 'vehicles.assign'],
            ['manager', 'drivers.view'], ['manager', 'drivers.create'], ['manager', 'drivers.update'],
            ['manager', 'files.view'], ['manager', 'files.upload'],
            ['manager', 'maintenances.view'], ['manager', 'maintenances.create'], ['manager', 'maintenances.update'], ['manager', 'maintenances.delete'], ['manager', 'maintenances.schedule'],
            ['manager', 'documents.view'], ['manager', 'documents.create'], ['manager', 'documents.update'], ['manager', 'documents.delete'],
            ['manager', 'fuel.view'], ['manager', 'fuel.create'], ['manager', 'fuel.update'], ['manager', 'fuel.delete'],
            ['manager', 'alerts.view'], ['manager', 'alerts.create'], ['manager', 'alerts.resolve'],
            ['manager', 'reports.view'], ['manager', 'reports.create'], ['manager', 'reports.export'], ['manager', 'reports.advanced'],
            ['manager', 'dashboard.view'], ['manager', 'dashboard.advanced'],
            ['manager', 'routes.view'], ['manager', 'routes.create'], ['manager', 'routes.update'], ['manager', 'routes.delete'],
            ['manager', 'tickets.view'], ['manager', 'tickets.create'], ['manager', 'tickets.update'], ['manager', 'tickets.delete'], ['manager', 'tickets.respond'],
        ]);

        // Driver
        $this->batchInsert('{{%auth_item_child}}', ['parent', 'child'], [
            ['driver', 'vehicles.view'],
            ['driver', 'files.view'],
            ['driver', 'fuel.view'], ['driver', 'fuel.create'],
            ['driver', 'documents.view'],
            ['driver', 'alerts.view'],
            ['driver', 'dashboard.view'],
            ['driver', 'routes.view'],
            ['driver', 'tickets.view'], ['driver', 'tickets.create'],
        ]);

        // Assign 'admin' role to user admin (user_id = 1)
        $this->insert('{{%auth_assignment}}', [
            'item_name' => 'admin',
            'user_id' => '1',
            'created_at' => $time,
        ]);

        // ============================================================================
        // SAMPLE DATA - DEMO CONTENT
        // ============================================================================
        /*
         * TEST USERS AVAILABLE:
         *
         * ADMIN:
         * - Username: admin
         * - Password: admin
         * - Role: admin (full administrator access)
         *
         * MANAGER:
         * - Username: manager
         * - Password: manager123
         * - Role: manager (fleet manager - manages vehicles, users, reports)
         *
         * DRIVERS:
         * - Username: driver1, driver2, driver3
         * - Password: driver123 (for all drivers)
         * - Role: driver (basic driver access - view vehicles, record fuel)
         */

        // Additional users (drivers and managers) - with different roles for testing
        $this->batchInsert('{{%users}}', [
            'id', 'company_id', 'name', 'username', 'email', 'password_hash', 'phone', 'status',
            'license_number', 'license_expiry', 'photo', 'roles', 'created_at'
        ], [
            // Manager user - username: manager / password: manager123
            [2, 1, 'Carlos Ferreira', 'manager', 'manager@veigest.com', '$2a$12$tHSe/ty2YB3VuLL0WswrAOQrNi0zifZzqtxpVsuLmYdl6XVatPU6G', '+351912345678', 'active', null, null, null, 'manager', date('Y-m-d H:i:s')],
            // Regular Driver 1 - username: driver1 / password: driver123
            [5, 1, 'Maria Santos', 'driver1', 'driver1@veigest.com', '$2a$12$8wh1Kv6CN3ZFob8XIjdezeSNV7OaC/ls/ZbZHS0ktf3PLDXrbboyy', '+351945678901', 'active', 'PT987654321', '2027-06-15', null, 'driver', date('Y-m-d H:i:s')],
            // Regular Driver 2 - username: driver2 / password: driver123
            [6, 1, 'Pedro Gomes', 'driver2', 'driver2@veigest.com', '$2a$12$8wh1Kv6CN3ZFob8XIjdezeSNV7OaC/ls/ZbZHS0ktf3PLDXrbboyy', '+351956789012', 'active', 'PT456789123', '2026-08-20', null, 'driver', date('Y-m-d H:i:s')],
            // Regular Driver 3 - username: driver3 / password: driver123
            [7, 1, 'Sofia Almeida', 'driver3', 'driver3@veigest.com', '$2a$12$8wh1Kv6CN3ZFob8XIjdezeSNV7OaC/ls/ZbZHS0ktf3PLDXrbboyy', '+351967890123', 'active', 'PT789123456', '2025-12-15', null, 'driver', date('Y-m-d H:i:s')],
        ]);

        // Assign roles to users (RBAC assignments)
        $this->batchInsert('{{%auth_assignment}}', ['item_name', 'user_id', 'created_at'], [
            ['manager', 2, $time],
            ['driver', 5, $time],
            ['driver', 6, $time],
            ['driver', 7, $time],
        ]);

        // Sample vehicles
        $this->batchInsert('{{%vehicles}}', [
            'id', 'company_id', 'license_plate', 'brand', 'model', 'year', 'fuel_type', 'mileage', 'status', 'driver_id', 'created_at'
        ], [
            [1, 1, 'AA-12-34', 'Mercedes-Benz', 'Sprinter', 2020, 'diesel', 125000, 'active', 5, date('Y-m-d H:i:s')], // assigned to driver1 (Maria Santos)
            [2, 1, 'BB-56-78', 'Volkswagen', 'Crafter', 2019, 'diesel', 98000, 'active', 6, date('Y-m-d H:i:s')], // assigned to driver2 (Pedro Gomes)
            [3, 1, 'CC-90-12', 'Iveco', 'Daily', 2021, 'diesel', 67000, 'active', 7, date('Y-m-d H:i:s')], // assigned to driver3 (Sofia Almeida)
            [4, 1, 'DD-34-56', 'Ford', 'Transit', 2018, 'diesel', 145000, 'maintenance', null, date('Y-m-d H:i:s')], // no driver assigned
        ]);

        // Sample files
        $this->batchInsert('{{%files}}', [
            'id', 'company_id', 'original_name', 'size', 'path', 'uploaded_by', 'created_at'
        ], [
            [1, 1, 'vehicle_insurance.pdf', 2048576, '/uploads/documents/vehicle_insurance.pdf', 1, date('Y-m-d H:i:s')],
            [2, 1, 'driver_license.jpg', 512000, '/uploads/documents/driver_license.jpg', 1, date('Y-m-d H:i:s')],
            [3, 1, 'vehicle_registration.pdf', 1536000, '/uploads/documents/vehicle_registration.pdf', 1, date('Y-m-d H:i:s')],
        ]);

        // Sample maintenances
        $this->batchInsert('{{%maintenances}}', [
            'id', 'company_id', 'vehicle_id', 'type', 'description', 'date', 'cost', 'mileage_record', 'next_date', 'workshop', 'created_at'
        ], [
            [1, 1, 1, 'Oil Change', 'Regular oil and filter change', '2025-10-15', 85.50, 120000, '2026-01-15', 'AutoCenter Lisbon', date('Y-m-d H:i:s')],
            [2, 1, 2, 'Tire Replacement', 'Replaced all 4 tires', '2025-09-20', 450.00, 95000, '2026-09-20', 'Pneus & Rodas', date('Y-m-d H:i:s')],
            [3, 1, 3, 'Brake Pads', 'Front brake pads replacement', '2025-11-01', 120.75, 65000, '2026-05-01', 'Freios Express', date('Y-m-d H:i:s')],
            [4, 1, 4, 'Engine Repair', 'Timing belt replacement', '2025-10-30', 680.00, 140000, '2026-10-30', 'MotorTech', date('Y-m-d H:i:s')],
        ]);

        // Sample documents
        $this->batchInsert('{{%documents}}', [
            'id', 'company_id', 'file_id', 'vehicle_id', 'driver_id', 'type', 'expiry_date', 'status', 'notes', 'created_at'
        ], [
            [1, 1, 1, 1, null, 'insurance', '2026-03-15', 'valid', 'Comprehensive insurance coverage', date('Y-m-d H:i:s')],
            [2, 1, 3, 1, null, 'registration', '2026-12-31', 'valid', 'Vehicle registration document', date('Y-m-d H:i:s')],
            [3, 1, 2, null, 5, 'license', '2027-06-15', 'valid', 'Driver license - Category C (Maria Santos)', date('Y-m-d H:i:s')],
            [4, 1, 1, 2, null, 'insurance', '2026-06-20', 'valid', 'Third party insurance', date('Y-m-d H:i:s')],
            [5, 1, 3, 3, null, 'inspection', '2026-02-28', 'valid', 'Annual technical inspection', date('Y-m-d H:i:s')],
        ]);

        // Sample fuel logs
        $this->batchInsert('{{%fuel_logs}}', [
            'id', 'company_id', 'vehicle_id', 'driver_id', 'date', 'liters', 'value', 'current_mileage', 'notes', 'created_at'
        ], [
            [1, 1, 1, 5, '2025-11-15', 45.50, 65.00, 125500, 'Regular fill-up by Maria Santos', date('Y-m-d H:i:s')],
            [2, 1, 2, 6, '2025-11-14', 52.30, 75.50, 98500, 'Highway trip by Pedro Gomes', date('Y-m-d H:i:s')],
            [3, 1, 3, 7, '2025-11-13', 38.75, 55.75, 67200, 'City delivery by Sofia Almeida', date('Y-m-d H:i:s')],
            [4, 1, 1, 5, '2025-11-10', 48.20, 69.50, 124800, 'Weekly fill-up by Maria Santos', date('Y-m-d H:i:s')],
        ]);

        // Sample alerts
        $this->batchInsert('{{%alerts}}', [
            'id', 'company_id', 'type', 'title', 'description', 'priority', 'status', 'details', 'created_at'
        ], [
            [1, 1, 'document', 'Insurance Expiring Soon', 'Vehicle AA-12-34 insurance expires in 15 days', 'high', 'active', '{"vehicle_id": 1, "document_id": 1}', date('Y-m-d H:i:s')],
            [2, 1, 'maintenance', 'Oil Change Due', 'Vehicle BB-56-78 needs oil change at 100,000 km', 'medium', 'active', '{"vehicle_id": 2, "next_mileage": 100000}', date('Y-m-d H:i:s')],
            [3, 1, 'document', 'Driver License Expires', 'Maria Santos license expires in 45 days', 'medium', 'active', '{"driver_id": 5, "document_id": 3}', date('Y-m-d H:i:s')],
            [4, 1, 'fuel', 'High Fuel Consumption', 'Vehicle CC-90-12 showing higher than average fuel consumption', 'low', 'active', '{"vehicle_id": 3}', date('Y-m-d H:i:s')],
        ]);

        // Sample routes
        $this->batchInsert('{{%routes}}', [
            'id', 'company_id', 'vehicle_id', 'driver_id', 'start_location', 'end_location', 'start_time', 'end_time', 'created_at'
        ], [
            [1, 1, 1, 5, 'Lisbon Warehouse', 'Porto Distribution Center', '2025-11-25 08:00:00', '2025-11-25 16:30:00', date('Y-m-d H:i:s')], // Maria Santos
            [2, 1, 2, 6, 'Lisbon Central', 'Coimbra Depot', '2025-11-26 07:30:00', '2025-11-26 15:45:00', date('Y-m-d H:i:s')], // Pedro Gomes
            [3, 1, 3, 7, 'Porto Hub', 'Viseu Warehouse', '2025-11-27 09:00:00', null, date('Y-m-d H:i:s')], // Sofia Almeida
            [4, 1, 1, 5, 'Lisbon Airport', 'Faro Terminal', '2025-11-28 06:00:00', '2025-11-28 18:00:00', date('Y-m-d H:i:s')], // Maria Santos
        ]);

        // Sample support tickets
        $this->batchInsert('{{%tickets}}', [
            'id', 'company_id', 'user_id', 'name', 'email', 'subject', 'body', 'priority', 'category', 'status', 'created_at'
        ], [
            [1, 1, 5, 'Maria Santos', 'driver1@veigest.com', 'Problema ao registar combustível', 
             'Estou a ter dificuldades para registar o abastecimento do veículo. O sistema apresenta um erro quando tento guardar.', 
             'high', 'technical', 'open', date('Y-m-d H:i:s', strtotime('-2 days'))],
            [2, 1, 6, 'Pedro Gomes', 'driver2@veigest.com', 'Dúvida sobre funcionalidades', 
             'Gostaria de saber como posso ver o histórico de manutenções do veículo que me foi atribuído.', 
             'low', 'other', 'open', date('Y-m-d H:i:s', strtotime('-1 day'))],
            [3, 1, 2, 'Carlos Ferreira', 'manager@veigest.com', 'Solicitação de novo relatório', 
             'Precisamos de um relatório que mostre o consumo de combustível por condutor. É possível adicionar esta funcionalidade?', 
             'medium', 'feature', 'in_progress', date('Y-m-d H:i:s', strtotime('-3 days'))],
            [4, 1, null, 'João Visitante', 'joao@exemplo.com', 'Informações sobre o sistema', 
             'Gostaria de obter mais informações sobre os planos disponíveis e funcionalidades do VeiGest.', 
             'low', 'other', 'open', date('Y-m-d H:i:s')],
        ]);

        // Sample activity logs
        $this->batchInsert('{{%activity_logs}}', [
            'id', 'company_id', 'user_id', 'action', 'entity', 'entity_id', 'details', 'ip', 'created_at'
        ], [
            [1, 1, 1, 'Created vehicle', 'vehicle', 1, '{"license_plate": "AA-12-34"}', '192.168.1.100', date('Y-m-d H:i:s')], // admin
            [2, 1, 1, 'Uploaded document', 'document', 1, '{"filename": "vehicle_insurance.pdf"}', '192.168.1.100', date('Y-m-d H:i:s')], // admin
            [3, 1, 2, 'Created maintenance', 'maintenance', 1, '{"vehicle_id": 1, "type": "Oil Change"}', '192.168.1.101', date('Y-m-d H:i:s')], // gestor
            [4, 1, 5, 'Recorded fuel', 'fuel_log', 1, '{"vehicle_id": 1, "liters": 45.5}', '192.168.1.102', date('Y-m-d H:i:s')], // Maria Santos (driver1)
            [5, 1, 2, 'Created route', 'route', 1, '{"start_location": "Lisbon Warehouse"}', '192.168.1.101', date('Y-m-d H:i:s')], // gestor
        ]);

        // Update user roles field based on RBAC assignments for better performance
        $this->execute("
            UPDATE users u
            SET u.roles = (
                SELECT GROUP_CONCAT(DISTINCT aa.item_name SEPARATOR ',')
                FROM auth_assignment aa
                WHERE aa.user_id = u.id
            )
            WHERE u.company_id = 1
        ");

        $this->execute('SET FOREIGN_KEY_CHECKS = 1');





    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');

        // Drop views
        $this->execute("DROP VIEW IF EXISTS v_vehicle_costs");
        $this->execute("DROP VIEW IF EXISTS v_company_stats");
        $this->execute("DROP VIEW IF EXISTS v_documents_expiring");

        // Drop tables in reverse order
        $this->dropTable('{{%tickets}}');
        $this->dropTable('{{%routes}}');
        $this->dropTable('{{%activity_logs}}');
        $this->dropTable('{{%alerts}}');
        $this->dropTable('{{%fuel_logs}}');
        $this->dropTable('{{%documents}}');
        $this->dropTable('{{%maintenances}}');
        $this->dropTable('{{%vehicles}}');
        $this->dropTable('{{%files}}');
        $this->dropTable('{{%users}}');
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
        $this->dropTable('{{%auth_rule}}');
        $this->dropTable('{{%companies}}');

        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
    }
}