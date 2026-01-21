<?php
/**
 * VeiGest Test Data - FuelLogs
 * Dados de teste para fixtures de abastecimentos
 */

return [
    [
        'id' => 1,
        'company_id' => 1,
        'vehicle_id' => 1,
        'driver_id' => 3,
        'date' => '2025-12-01',
        'liters' => 45.5,
        'value' => 72.80,
        'price_per_liter' => 1.60,
        'current_mileage' => 49500,
        'notes' => 'Abastecimento completo',
        'created_at' => '2025-12-01 10:30:00',
    ],
    [
        'id' => 2,
        'company_id' => 1,
        'vehicle_id' => 1,
        'driver_id' => 3,
        'date' => '2025-12-15',
        'liters' => 42.0,
        'value' => 68.04,
        'price_per_liter' => 1.62,
        'current_mileage' => 50000,
        'notes' => null,
        'created_at' => '2025-12-15 14:20:00',
    ],
    [
        'id' => 3,
        'company_id' => 1,
        'vehicle_id' => 2,
        'driver_id' => null,
        'date' => '2025-11-25',
        'liters' => 38.0,
        'value' => 65.36,
        'price_per_liter' => 1.72,
        'current_mileage' => 34800,
        'notes' => 'Gasolina 95',
        'created_at' => '2025-11-25 09:15:00',
    ],
];
