<?php

/**
 * User fixture data
 * Utilizadores para testes (admin, manager, driver)
 * NOTA: O role é gerido pelo RBAC (tabela auth_assignment), não na tabela users
 */
return [
    'admin' => [
        'id' => 1,
        'username' => 'admin',
        'name' => 'Administrator',
        'email' => 'admin@veigest.com',
        'company_id' => 1,
        'status' => 'active',
        // password: admin
        'password_hash' => '$2a$12$yjs8TTsveJPiMeAg1D5fNePfO9rPOKmRRDBnW1xUFfss/NEPZhvEa',
        'auth_key' => 'test-admin-auth-key',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    'manager' => [
        'id' => 2,
        'username' => 'manager',
        'name' => 'Carlos Ferreira',
        'email' => 'manager@veigest.com',
        'company_id' => 1,
        'status' => 'active',
        // password: manager123
        'password_hash' => '$2a$12$tHSe/ty2YB3VuLL0WswrAOQrNi0zifZzqtxpVsuLmYdl6XVatPU6G',
        'auth_key' => 'test-manager-auth-key',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    'driver' => [
        'id' => 3,
        'username' => 'driver1',
        'name' => 'Maria Santos',
        'email' => 'driver@veigest.com',
        'company_id' => 1,
        'status' => 'active',
        // password: driver123
        'password_hash' => '$2a$12$8wh1Kv6CN3ZFob8XIjdezeSNV7OaC/ls/ZbZHS0ktf3PLDXrbboyy',
        'auth_key' => 'test-driver-auth-key',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
