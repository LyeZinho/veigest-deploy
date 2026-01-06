<?php
/**
 * VeiGest Test Data - Maintenances
 * Dados de teste para fixtures de manutenções
 */

return [
    [
        'id' => 1,
        'company_id' => 1,
        'vehicle_id' => 1,
        'type' => 'preventive',
        'description' => 'Revisão geral dos 50.000km',
        'date' => '2025-12-15',
        'cost' => 250.00,
        'mileage_record' => 50000,
        'next_date' => '2026-06-15',
        'workshop' => 'Oficina Central',
        'status' => 'scheduled',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    [
        'id' => 2,
        'company_id' => 1,
        'vehicle_id' => 2,
        'type' => 'corrective',
        'description' => 'Troca de travões',
        'date' => '2025-11-20',
        'cost' => 450.00,
        'mileage_record' => 35000,
        'next_date' => null,
        'workshop' => 'Auto Reparações Lda',
        'status' => 'completed',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    [
        'id' => 3,
        'company_id' => 1,
        'vehicle_id' => 3,
        'type' => 'inspection',
        'description' => 'Inspeção periódica obrigatória',
        'date' => '2026-01-10',
        'cost' => 30.00,
        'mileage_record' => 15000,
        'next_date' => '2027-01-10',
        'workshop' => 'Centro Inspeção Autorizado',
        'status' => 'scheduled',
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
