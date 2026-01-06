<?php
/**
 * VeiGest Test Data - Users
 * Dados de teste para fixtures de utilizadores
 * 
 * Status: 'active' (ativo) ou 'inactive' (inativo)
 * Roles: 'admin', 'manager', 'driver'
 */

return [
    // Administrador do sistema
    [
        'id' => 1,
        'username' => 'admin',
        'name' => 'Administrador',
        'email' => 'admin@veigest.test',
        'company_id' => 1,
        'auth_key' => 'test_admin_auth_key_12345',
        'password_hash' => '$2a$12$yjs8TTsveJPiMeAg1D5fNePfO9rPOKmRRDBnW1xUFfss/NEPZhvEa', // admin
        'status' => 'active',
        'roles' => 'admin',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    // Manager da empresa
    [
        'id' => 2,
        'username' => 'manager',
        'name' => 'Carlos Ferreira',
        'email' => 'manager@veigest.test',
        'company_id' => 1,
        'auth_key' => 'test_manager_auth_key_12345',
        'password_hash' => '$2a$12$tHSe/ty2YB3VuLL0WswrAOQrNi0zifZzqtxpVsuLmYdl6XVatPU6G', // manager123
        'status' => 'active',
        'roles' => 'manager',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    // Condutor ativo
    [
        'id' => 3,
        'username' => 'driver1',
        'name' => 'Maria Santos',
        'email' => 'driver1@veigest.test',
        'company_id' => 1,
        'auth_key' => 'test_driver1_auth_key_12345',
        'password_hash' => '$2a$12$8wh1Kv6CN3ZFob8XIjdezeSNV7OaC/ls/ZbZHS0ktf3PLDXrbboyy', // driver123
        'status' => 'active',
        'roles' => 'driver',
        'license_number' => 'L-123456',
        'license_expiry' => '2027-12-31',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    // Condutor inativo
    [
        'id' => 4,
        'username' => 'driver_inactive',
        'name' => 'João Inativo',
        'email' => 'inactive@veigest.test',
        'company_id' => 1,
        'auth_key' => 'test_inactive_auth_key_12345',
        'password_hash' => '$2a$12$8wh1Kv6CN3ZFob8XIjdezeSNV7OaC/ls/ZbZHS0ktf3PLDXrbboyy', // driver123
        'status' => 'inactive',
        'roles' => 'driver',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
