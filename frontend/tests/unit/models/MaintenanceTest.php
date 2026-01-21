<?php

namespace frontend\tests\unit\models;

use Yii;
use common\models\Maintenance;
use common\models\Vehicle;
use frontend\tests\fixtures\MaintenanceFixture;
use frontend\tests\fixtures\VehicleFixture;
use frontend\tests\fixtures\CompanyFixture;
use frontend\tests\fixtures\UserFixture;
use Codeception\Test\Unit;

/**
 * Maintenance Model Unit Test
 * 
 * RF-TT-001: Testes unitários para modelo Maintenance
 * Testa validações e operações de manutenções
 * 
 * @property \frontend\tests\UnitTester $tester
 */
class MaintenanceTest extends Unit
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
            'maintenance' => MaintenanceFixture::class,
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica que company_id, vehicle_id, type e date são obrigatórios
     */
    public function testRequiredFieldsValidation()
    {
        $maintenance = new Maintenance();
        
        // Model sem dados não deve validar
        $this->assertFalse($maintenance->validate());
        
        // Verificar erros de campos obrigatórios
        $this->assertArrayHasKey('company_id', $maintenance->errors);
        $this->assertArrayHasKey('vehicle_id', $maintenance->errors);
        $this->assertArrayHasKey('type', $maintenance->errors);
        $this->assertArrayHasKey('date', $maintenance->errors);
    }

    /**
     * Teste 2: Validação de custo não negativo
     * Verifica que cost deve ser número positivo ou zero
     */
    public function testCostValidation()
    {
        $maintenance = new Maintenance([
            'company_id' => 1,
            'vehicle_id' => 1,
            'type' => 'preventive',
            'date' => '2025-12-20',
        ]);
        
        // Custo válido (zero)
        $maintenance->cost = 0;
        $this->assertTrue($maintenance->validate(['cost']));
        
        // Custo válido (positivo)
        $maintenance->cost = 150.50;
        $this->assertTrue($maintenance->validate(['cost']));
        
        // Custo default deve ser 0
        $newMaintenance = new Maintenance();
        $newMaintenance->validate();
        $this->assertEquals(0, $newMaintenance->cost);
    }

    /**
     * Teste 3: Teste de integração com BD - CRUD
     * Testa Create, Read, Update, Delete de manutenção
     */
    public function testMaintenanceCRUD()
    {
        // CREATE - Criar nova manutenção
        $maintenance = new Maintenance([
            'company_id' => 1,
            'vehicle_id' => 1,
            'type' => 'oil_change',
            'description' => 'Troca de óleo e filtros',
            'date' => '2026-01-15',
            'cost' => 85.00,
            'mileage_record' => 52000,
            'workshop' => 'Quick Lube',
            'status' => 'scheduled',
        ]);
        
        $this->assertTrue($maintenance->save(), 'Deveria salvar a manutenção');
        $this->assertNotNull($maintenance->id);
        $savedId = $maintenance->id;
        
        // READ - Buscar manutenção criada
        $found = Maintenance::findOne($savedId);
        $this->assertNotNull($found);
        $this->assertEquals('oil_change', $found->type);
        $this->assertEquals(85.00, $found->cost);
        
        // UPDATE - Alterar status para concluído
        $found->status = 'completed';
        $found->cost = 90.00; // Custo final diferente
        $this->assertTrue($found->save());
        
        $updated = Maintenance::findOne($savedId);
        $this->assertEquals('completed', $updated->status);
        $this->assertEquals(90.00, $updated->cost);
        
        // DELETE - Remover manutenção
        $this->assertEquals(1, $updated->delete());
        $this->assertNull(Maintenance::findOne($savedId));
    }

    /**
     * Teste 4: Relacionamento com Vehicle
     * Verifica que manutenção tem relacionamento correto com veículo
     */
    public function testVehicleRelationship()
    {
        $maintenance = Maintenance::findOne(1); // Fixture
        
        $this->assertNotNull($maintenance);
        $this->assertNotNull($maintenance->vehicle);
        $this->assertEquals(1, $maintenance->vehicle->id);
        $this->assertEquals('AA-00-AA', $maintenance->vehicle->license_plate);
    }

    /**
     * Teste 5: Verificar labels de status
     * Testa método getStatusLabels
     */
    public function testStatusLabels()
    {
        $labels = Maintenance::getStatusLabels();
        
        $this->assertIsArray($labels);
        $this->assertArrayHasKey(Maintenance::STATUS_SCHEDULED, $labels);
        $this->assertArrayHasKey(Maintenance::STATUS_COMPLETED, $labels);
        $this->assertArrayHasKey(Maintenance::STATUS_CANCELLED, $labels);
        
        $this->assertEquals('Agendada', $labels[Maintenance::STATUS_SCHEDULED]);
        $this->assertEquals('Concluída', $labels[Maintenance::STATUS_COMPLETED]);
    }

    /**
     * Teste 6: Verificar labels de tipos de manutenção
     * Testa método getTypeLabels
     */
    public function testTypeLabels()
    {
        $labels = Maintenance::getTypeLabels();
        
        $this->assertIsArray($labels);
        $this->assertArrayHasKey(Maintenance::TYPE_PREVENTIVE, $labels);
        $this->assertArrayHasKey(Maintenance::TYPE_CORRECTIVE, $labels);
        $this->assertArrayHasKey(Maintenance::TYPE_INSPECTION, $labels);
        
        $this->assertEquals('Preventiva', $labels[Maintenance::TYPE_PREVENTIVE]);
        $this->assertEquals('Corretiva', $labels[Maintenance::TYPE_CORRECTIVE]);
    }
}
