<?php

namespace frontend\tests\unit\models;

use Yii;
use common\models\FuelLog;
use frontend\tests\fixtures\FuelLogFixture;
use frontend\tests\fixtures\VehicleFixture;
use frontend\tests\fixtures\CompanyFixture;
use frontend\tests\fixtures\UserFixture;
use Codeception\Test\Unit;

/**
 * FuelLog Model Unit Test
 * 
 * RF-TT-001: Testes unitários para modelo FuelLog
 * Testa validações e operações de abastecimentos
 * 
 * @property \frontend\tests\UnitTester $tester
 */
class FuelLogTest extends Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;

    /**
     * Load fixtures before each test
     */
    public function _fixtures()
    {
        return [
            'company' => CompanyFixture::class,
            'user' => UserFixture::class,
            'vehicle' => VehicleFixture::class,
            'fuel_log' => FuelLogFixture::class,
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica que company_id, vehicle_id, date, liters e value são obrigatórios
     */
    public function testRequiredFieldsValidation()
    {
        $fuelLog = new FuelLog();
        
        // Model sem dados não deve validar
        $this->assertFalse($fuelLog->validate());
        
        // Verificar erros de campos obrigatórios
        $this->assertArrayHasKey('company_id', $fuelLog->errors);
        $this->assertArrayHasKey('vehicle_id', $fuelLog->errors);
        $this->assertArrayHasKey('date', $fuelLog->errors);
        $this->assertArrayHasKey('liters', $fuelLog->errors);
        $this->assertArrayHasKey('value', $fuelLog->errors);
    }

    /**
     * Teste 2: Validação de litros e valor numéricos
     * Verifica que liters e value devem ser números
     */
    public function testNumericFieldsValidation()
    {
        $fuelLog = new FuelLog([
            'company_id' => 1,
            'vehicle_id' => 1,
            'date' => '2025-12-20',
        ]);
        
        // Litros válidos
        $fuelLog->liters = 45.5;
        $fuelLog->value = 75.00;
        $this->assertTrue($fuelLog->validate(['liters', 'value']));
        
        // Valores inteiros também são válidos
        $fuelLog->liters = 50;
        $fuelLog->value = 80;
        $this->assertTrue($fuelLog->validate(['liters', 'value']));
    }

    /**
     * Teste 3: Cálculo automático de preço por litro
     * Verifica método beforeSave calcula price_per_liter
     */
    public function testPricePerLiterCalculation()
    {
        $fuelLog = new FuelLog([
            'company_id' => 1,
            'vehicle_id' => 1,
            'date' => '2026-01-05',
            'liters' => 40.0,
            'value' => 64.00,
            'current_mileage' => 51000,
        ]);
        
        // Salvar para trigger do beforeSave
        $this->assertTrue($fuelLog->save());
        
        // Verificar cálculo: 64.00 / 40.0 = 1.60
        $this->assertEquals(1.60, $fuelLog->price_per_liter);
        
        // Limpar
        $fuelLog->delete();
    }

    /**
     * Teste 4: Teste de integração com BD - CRUD
     * Testa Create, Read, Update, Delete de abastecimento
     */
    public function testFuelLogCRUD()
    {
        // CREATE - Criar novo abastecimento
        $fuelLog = new FuelLog([
            'company_id' => 1,
            'vehicle_id' => 1,
            'driver_id' => 3,
            'date' => '2026-01-10',
            'liters' => 35.0,
            'value' => 59.50,
            'current_mileage' => 51500,
            'notes' => 'Teste CRUD',
        ]);
        
        $this->assertTrue($fuelLog->save(), 'Deveria salvar o abastecimento');
        $this->assertNotNull($fuelLog->id);
        $savedId = $fuelLog->id;
        
        // READ - Buscar abastecimento criado
        $found = FuelLog::findOne($savedId);
        $this->assertNotNull($found);
        $this->assertEquals(35.0, $found->liters);
        $this->assertEquals('Teste CRUD', $found->notes);
        
        // UPDATE - Alterar quilometragem
        $found->current_mileage = 51600;
        $this->assertTrue($found->save());
        
        $updated = FuelLog::findOne($savedId);
        $this->assertEquals(51600, $updated->current_mileage);
        
        // DELETE - Remover abastecimento
        $this->assertEquals(1, $updated->delete());
        $this->assertNull(FuelLog::findOne($savedId));
    }

    /**
     * Teste 5: Relacionamentos com Vehicle e Driver
     * Verifica relacionamentos corretos
     */
    public function testRelationships()
    {
        $fuelLog = FuelLog::findOne(1); // Fixture
        
        $this->assertNotNull($fuelLog);
        
        // Relacionamento com veículo
        $this->assertNotNull($fuelLog->vehicle);
        $this->assertEquals(1, $fuelLog->vehicle->id);
        $this->assertEquals('AA-00-AA', $fuelLog->vehicle->license_plate);
        
        // Relacionamento com motorista
        $this->assertNotNull($fuelLog->driver);
        $this->assertEquals(3, $fuelLog->driver->id);
    }

    /**
     * Teste 6: Validação de foreign keys
     * Verifica que vehicle_id e company_id devem existir
     */
    public function testForeignKeyValidation()
    {
        $fuelLog = new FuelLog([
            'company_id' => 99999, // Não existe
            'vehicle_id' => 99999, // Não existe
            'date' => '2026-01-20',
            'liters' => 30.0,
            'value' => 50.00,
        ]);
        
        $this->assertFalse($fuelLog->validate());
        $this->assertArrayHasKey('company_id', $fuelLog->errors);
        $this->assertArrayHasKey('vehicle_id', $fuelLog->errors);
    }
}
