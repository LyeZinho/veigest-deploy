<?php
/**
 * VeiGest Test Data - Vehicles
 * Dados de teste para fixtures de veículos
 */

return [
    [
        'id' => 1,
        'company_id' => 1,
        'license_plate' => 'AA-00-AA',
        'brand' => 'Volkswagen',
        'model' => 'Golf',
        'year' => 2022,
        'fuel_type' => 'diesel',
        'mileage' => 50000,
        'status' => 'active',
        'driver_id' => 3, // driver1
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    [
        'id' => 2,
        'company_id' => 1,
        'license_plate' => 'BB-11-BB',
        'brand' => 'Renault',
        'model' => 'Megane',
        'year' => 2021,
        'fuel_type' => 'gasoline',
        'mileage' => 35000,
        'status' => 'maintenance',
        'driver_id' => null,
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
    [
        'id' => 3,
        'company_id' => 1,
        'license_plate' => 'CC-22-CC',
        'brand' => 'Tesla',
        'model' => 'Model 3',
        'year' => 2023,
        'fuel_type' => 'electric',
        'mileage' => 15000,
        'status' => 'active',
        'driver_id' => null,
        'created_at' => '2025-01-01 00:00:00',
        'updated_at' => '2025-01-01 00:00:00',
    ],
];
