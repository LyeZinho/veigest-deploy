<?php

namespace frontend\tests\unit\models;

use Yii;
use common\models\Vehicle;
use frontend\tests\fixtures\VehicleFixture;
use frontend\tests\fixtures\CompanyFixture;
use frontend\tests\fixtures\UserFixture;
use Codeception\Test\Unit;

/**
 * Vehicle Model Unit Test
 * 
 * RF-TT-001: Testes unitários para modelo Vehicle
 * Testa validações de veículos e integração com BD
 * 
 * @property \frontend\tests\UnitTester $tester
 */
class VehicleTest extends Unit
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
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica que company_id, license_plate, brand, model são obrigatórios
     */
    public function testRequiredFieldsValidation()
    {
        $vehicle = new Vehicle();
        
        // Model sem dados não deve validar
        $this->assertFalse($vehicle->validate());
        
        // Verificar erros de campos obrigatórios
        $this->assertArrayHasKey('company_id', $vehicle->errors);
        $this->assertArrayHasKey('license_plate', $vehicle->errors);
        $this->assertArrayHasKey('brand', $vehicle->errors);
        $this->assertArrayHasKey('model', $vehicle->errors);
    }

    /**
     * Teste 2: Validação de tipo de combustível
     * Verifica que fuel_type deve estar na lista permitida
     */
    public function testFuelTypeValidation()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'XX-99-XX',
            'brand' => 'Test',
            'model' => 'Test Model',
        ]);
        
        // Tipo de combustível inválido
        $vehicle->fuel_type = 'nuclear';
        $this->assertFalse($vehicle->validate(['fuel_type']));
        
        // Tipos válidos
        $validFuelTypes = [
            Vehicle::FUEL_GASOLINE,
            Vehicle::FUEL_DIESEL,
            Vehicle::FUEL_ELECTRIC,
            Vehicle::FUEL_HYBRID,
            Vehicle::FUEL_OTHER,
        ];
        
        foreach ($validFuelTypes as $fuelType) {
            $vehicle->fuel_type = $fuelType;
            $this->assertTrue($vehicle->validate(['fuel_type']), "Fuel type '$fuelType' deveria ser válido");
        }
    }

    /**
     * Teste 3: Validação de status do veículo
     * Verifica que status deve ser active, maintenance ou inactive
     */
    public function testStatusValidation()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'YY-88-YY',
            'brand' => 'Status',
            'model' => 'Test',
        ]);
        
        // Status inválido
        $vehicle->status = 'destroyed';
        $this->assertFalse($vehicle->validate(['status']));
        
        // Status válidos
        $validStatuses = [
            Vehicle::STATUS_ACTIVE,
            Vehicle::STATUS_MAINTENANCE,
            Vehicle::STATUS_INACTIVE,
        ];
        
        foreach ($validStatuses as $status) {
            $vehicle->status = $status;
            $this->assertTrue($vehicle->validate(['status']), "Status '$status' deveria ser válido");
        }
    }

    /**
     * Teste 4: Validação do ano do veículo
     * Verifica range válido de anos (1900-2030)
     */
    public function testYearRangeValidation()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'ZZ-77-ZZ',
            'brand' => 'Year',
            'model' => 'Test',
        ]);
        
        // Ano muito antigo
        $vehicle->year = 1800;
        $this->assertFalse($vehicle->validate(['year']));
        
        // Ano futuro demais
        $vehicle->year = 2050;
        $this->assertFalse($vehicle->validate(['year']));
        
        // Ano válido
        $vehicle->year = 2023;
        $this->assertTrue($vehicle->validate(['year']));
    }

    /**
     * Teste 5: Teste de integração com BD - CRUD
     * Testa Create, Read, Update, Delete de veículo
     */
    public function testVehicleCRUD()
    {
        // CREATE - Criar novo veículo
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'NEW-01-TST',
            'brand' => 'TestBrand',
            'model' => 'TestModel',
            'year' => 2024,
            'fuel_type' => 'diesel',
            'mileage' => 0,
            'status' => 'active',
        ]);
        
        $this->assertTrue($vehicle->save(), 'Deveria salvar o veículo');
        $this->assertNotNull($vehicle->id);
        $savedId = $vehicle->id;
        
        // READ - Buscar veículo criado
        $foundVehicle = Vehicle::findOne($savedId);
        $this->assertNotNull($foundVehicle);
        $this->assertEquals('NEW-01-TST', $foundVehicle->license_plate);
        $this->assertEquals('TestBrand', $foundVehicle->brand);
        
        // UPDATE - Atualizar quilometragem
        $foundVehicle->mileage = 1000;
        $this->assertTrue($foundVehicle->save());
        
        $updatedVehicle = Vehicle::findOne($savedId);
        $this->assertEquals(1000, $updatedVehicle->mileage);
        
        // DELETE - Remover veículo
        $this->assertEquals(1, $updatedVehicle->delete());
        $this->assertNull(Vehicle::findOne($savedId));
    }

    /**
     * Teste 6: Relacionamento com Company
     * Verifica que veículo tem relacionamento correto com empresa
     */
    public function testCompanyRelationship()
    {
        $vehicle = Vehicle::findOne(1); // Fixture
        
        $this->assertNotNull($vehicle);
        $this->assertNotNull($vehicle->company);
        $this->assertEquals(1, $vehicle->company->id);
    }
}
