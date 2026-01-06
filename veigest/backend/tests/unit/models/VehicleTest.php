<?php

namespace backend\tests\unit\models;

use Yii;
use backend\tests\UnitTester;
use common\models\Vehicle;
use backend\tests\fixtures\VehicleFixture;
use backend\tests\fixtures\CompanyFixture;
use backend\tests\fixtures\UserFixture;

/**
 * Teste Unitário #2: VehicleTest
 * 
 * RF-TT-001: Testes unitários para o modelo Vehicle
 * - Validação de parâmetros de entrada
 * - Integração com Base de Dados (Active Record)
 */
class VehicleTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    /**
     * Fixtures necessárias para os testes
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
     * Teste 2.1: Validação de campos obrigatórios
     */
    public function testValidationRulesRequired()
    {
        $vehicle = new Vehicle();
        
        // Modelo vazio não deve validar
        $this->assertFalse($vehicle->validate());
        
        // Deve ter erros nos campos obrigatórios
        $this->assertArrayHasKey('company_id', $vehicle->errors);
        $this->assertArrayHasKey('license_plate', $vehicle->errors);
        $this->assertArrayHasKey('brand', $vehicle->errors);
        $this->assertArrayHasKey('model', $vehicle->errors);
    }

    /**
     * Teste 2.2: Validação de fuel_type
     * Verifica se o modelo aceita apenas tipos de combustível válidos
     */
    public function testValidationFuelType()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'XX-00-XX',
            'brand' => 'Test',
            'model' => 'Test',
            'fuel_type' => 'nuclear', // inválido
        ]);
        
        $this->assertFalse($vehicle->validate(['fuel_type']));
        
        // Tipos válidos
        $validTypes = ['gasoline', 'diesel', 'electric', 'hybrid', 'other'];
        foreach ($validTypes as $type) {
            $vehicle->fuel_type = $type;
            $this->assertTrue($vehicle->validate(['fuel_type']), "Fuel type '$type' deveria ser válido");
        }
    }

    /**
     * Teste 2.3: Validação de status
     */
    public function testValidationStatus()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'XX-00-XX',
            'brand' => 'Test',
            'model' => 'Test',
            'status' => 'destroyed', // inválido
        ]);
        
        $this->assertFalse($vehicle->validate(['status']));
        
        // Status válidos
        $validStatuses = ['active', 'maintenance', 'inactive'];
        foreach ($validStatuses as $status) {
            $vehicle->status = $status;
            $this->assertTrue($vehicle->validate(['status']), "Status '$status' deveria ser válido");
        }
    }

    /**
     * Teste 2.4: Validação de ano do veículo
     */
    public function testValidationYear()
    {
        $vehicle = new Vehicle([
            'company_id' => 1,
            'license_plate' => 'XX-00-XX',
            'brand' => 'Test',
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
     * Teste 2.5: Busca de veículo por ID (integração BD)
     */
    public function testFindById()
    {
        $vehicle = Vehicle::findOne(1);
        
        $this->assertNotNull($vehicle);
        $this->assertEquals('AA-00-AA', $vehicle->license_plate);
        $this->assertEquals('Renault', $vehicle->brand);
        $this->assertEquals('active', $vehicle->status);
    }

    /**
     * Teste 2.6: Relacionamento com Company (integração BD)
     */
    public function testCompanyRelation()
    {
        $vehicle = Vehicle::findOne(1);
        
        $this->assertNotNull($vehicle);
        $this->assertNotNull($vehicle->company);
        $this->assertEquals('Empresa Teste', $vehicle->company->name);
    }
}
