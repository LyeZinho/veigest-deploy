<?php

/**
 * Auth Assignment fixture data - RBAC user roles
 * Atribui roles aos utilizadores de teste
 */
return [
    'admin_role' => [
        'item_name' => 'admin',
        'user_id' => '1',
        'created_at' => strtotime('2025-01-01 00:00:00'),
    ],
    'manager_role' => [
        'item_name' => 'manager',
        'user_id' => '2',
        'created_at' => strtotime('2025-01-01 00:00:00'),
    ],
    'driver_role' => [
        'item_name' => 'driver',
        'user_id' => '3',
        'created_at' => strtotime('2025-01-01 00:00:00'),
    ],
];
