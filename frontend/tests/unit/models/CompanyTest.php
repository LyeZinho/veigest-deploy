<?php

namespace frontend\tests\unit\models;

use Yii;
use common\models\Company;
use frontend\tests\fixtures\CompanyFixture;
use Codeception\Test\Unit;

/**
 * Company Model Unit Test
 * 
 * RF-TT-001: Testes unitários para modelo Company
 * Testa validações e operações de empresas
 * 
 * @property \frontend\tests\UnitTester $tester
 */
class CompanyTest extends Unit
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
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica que name e tax_id são obrigatórios
     */
    public function testRequiredFieldsValidation()
    {
        $company = new Company();
        
        // Model sem dados não deve validar
        $this->assertFalse($company->validate());
        
        // Verificar erros de campos obrigatórios
        $this->assertArrayHasKey('name', $company->errors);
        $this->assertArrayHasKey('tax_id', $company->errors);
    }

    /**
     * Teste 2: Validação de formato de email
     * Verifica que email deve ter formato válido
     */
    public function testEmailFormatValidation()
    {
        $company = new Company([
            'name' => 'Test Company',
            'tax_id' => '123456789',
        ]);
        
        // Email inválido
        $company->email = 'invalid-email';
        $this->assertFalse($company->validate(['email']));
        
        // Email válido
        $company->email = 'valid@company.com';
        $this->assertTrue($company->validate(['email']));
        
        // Email pode ser nulo
        $company->email = null;
        $this->assertTrue($company->validate(['email']));
    }

    /**
     * Teste 3: Validação de status
     * Verifica que status deve estar em active, suspended, inactive
     */
    public function testStatusValidation()
    {
        $company = new Company([
            'name' => 'Status Test',
            'tax_id' => '111222333',
        ]);
        
        // Status inválido
        $company->status = 'deleted';
        $this->assertFalse($company->validate(['status']));
        
        // Status válidos
        $validStatuses = ['active', 'suspended', 'inactive'];
        foreach ($validStatuses as $status) {
            $company->status = $status;
            $this->assertTrue($company->validate(['status']), "Status '$status' deveria ser válido");
        }
        
        // Status default é active
        $newCompany = new Company();
        $newCompany->validate();
        $this->assertEquals('active', $newCompany->status);
    }

    /**
     * Teste 4: Validação de plano
     * Verifica que plan deve estar em basic, professional, enterprise
     */
    public function testPlanValidation()
    {
        $company = new Company([
            'name' => 'Plan Test',
            'tax_id' => '444555666',
        ]);
        
        // Plano inválido
        $company->plan = 'premium';
        $this->assertFalse($company->validate(['plan']));
        
        // Planos válidos
        $validPlans = ['basic', 'professional', 'enterprise'];
        foreach ($validPlans as $plan) {
            $company->plan = $plan;
            $this->assertTrue($company->validate(['plan']), "Plan '$plan' deveria ser válido");
        }
        
        // Plan default é basic
        $newCompany = new Company();
        $newCompany->validate();
        $this->assertEquals('basic', $newCompany->plan);
    }

    /**
     * Teste 5: Teste de integração com BD - CRUD
     * Testa Create, Read, Update, Delete de empresa
     */
    public function testCompanyCRUD()
    {
        // CREATE - Criar nova empresa
        $company = new Company([
            'code' => 'TEST001',
            'name' => 'Test Company CRUD',
            'tax_id' => '777888999',
            'email' => 'crud@test.com',
            'phone' => '+351911222333',
            'status' => 'active',
            'plan' => 'professional',
        ]);
        
        $this->assertTrue($company->save(), 'Deveria salvar a empresa');
        $this->assertNotNull($company->id);
        $savedId = $company->id;
        
        // READ - Buscar empresa criada
        $found = Company::findOne($savedId);
        $this->assertNotNull($found);
        $this->assertEquals('Test Company CRUD', $found->name);
        $this->assertEquals('777888999', $found->tax_id);
        
        // UPDATE - Alterar plano
        $found->plan = 'enterprise';
        $this->assertTrue($found->save());
        
        $updated = Company::findOne($savedId);
        $this->assertEquals('enterprise', $updated->plan);
        
        // DELETE - Remover empresa
        $this->assertEquals(1, $updated->delete());
        $this->assertNull(Company::findOne($savedId));
    }

    /**
     * Teste 6: Validação de tamanho máximo dos campos
     * Verifica limites de caracteres
     */
    public function testFieldLengthValidation()
    {
        $company = new Company([
            'name' => str_repeat('A', 201), // max 200
            'tax_id' => str_repeat('1', 21), // max 20
            'email' => str_repeat('a', 140) . '@test.com', // max 150
            'phone' => str_repeat('1', 21), // max 20
        ]);
        
        $this->assertFalse($company->validate());
        $this->assertArrayHasKey('name', $company->errors);
        $this->assertArrayHasKey('tax_id', $company->errors);
    }
}
