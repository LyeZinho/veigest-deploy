<?php

/**
 * Vehicle fixture data
 */
return [
    'vehicle1' => [
        'id' => 1,
        'company_id' => 1,
        'license_plate' => 'AA-00-AA',
        'brand' => 'Renault',
        'model' => 'Clio',
        'year' => 2022,
        'fuel_type' => 'gasoline',
        'mileage' => 50000,
        'status' => 'active',
        'driver_id' => null,
        'created_at' => '2025-01-01 00:00:00',
    ],
    'vehicle2' => [
        'id' => 2,
        'company_id' => 1,
        'license_plate' => 'BB-11-BB',
        'brand' => 'Peugeot',
        'model' => '308',
        'year' => 2021,
        'fuel_type' => 'diesel',
        'mileage' => 75000,
        'status' => 'maintenance',
        'driver_id' => 3,
        'created_at' => '2025-01-01 00:00:00',
    ],
];
