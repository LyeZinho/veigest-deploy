<?php
/**
 * VeiGest Test Data - Companies
 * Dados de teste para fixtures de empresas
 */

return [
    [
        'id' => 1,
        'code' => 'VEIGEST001',
        'name' => 'VeiGest Demo Company',
        'tax_id' => '123456789',
        'email' => 'demo@veigest.test',
        'phone' => '+351912345678',
        'status' => 'active',
        'plan' => 'professional',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    [
        'id' => 2,
        'code' => 'VEIGEST002',
        'name' => 'Empresa Teste Inativa',
        'tax_id' => '987654321',
        'email' => 'inactive@veigest.test',
        'phone' => '+351919876543',
        'status' => 'inactive',
        'plan' => 'basic',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
