<?php

namespace backend\tests\unit\models;

use Yii;
use backend\tests\UnitTester;
use common\models\Company;
use backend\tests\fixtures\CompanyFixture;

/**
 * Teste Unitário #3: CompanyTest
 * 
 * RF-TT-001: Testes unitários para o modelo Company
 * - Validação de parâmetros de entrada
 * - Integração com Base de Dados (Active Record)
 */
class CompanyTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    /**
     * Fixtures necessárias para os testes
     */
    public function _fixtures()
    {
        return [
            'company' => CompanyFixture::class,
        ];
    }

    /**
     * Teste 3.1: Validação de campos obrigatórios
     */
    public function testValidationRulesRequired()
    {
        $company = new Company();
        
        // Modelo vazio não deve validar
        $this->assertFalse($company->validate());
        
        // Deve ter erros nos campos obrigatórios
        $this->assertArrayHasKey('name', $company->errors);
        $this->assertArrayHasKey('tax_id', $company->errors);
    }

    /**
     * Teste 3.2: Validação de email da empresa
     */
    public function testValidationEmail()
    {
        $company = new Company([
            'name' => 'Empresa Teste',
            'tax_id' => '999999999',
            'email' => 'email-invalido',
        ]);
        
        $this->assertFalse($company->validate(['email']));
        
        // Email válido deve passar
        $company->email = 'empresa@teste.com';
        $this->assertTrue($company->validate(['email']));
    }

    /**
     * Teste 3.3: Validação de status
     */
    public function testValidationStatus()
    {
        $company = new Company([
            'name' => 'Empresa Teste',
            'tax_id' => '999999999',
            'status' => 'invalid_status',
        ]);
        
        $this->assertFalse($company->validate(['status']));
        
        // Status válidos
        foreach (['active', 'suspended', 'inactive'] as $status) {
            $company->status = $status;
            $this->assertTrue($company->validate(['status']), "Status '$status' deveria ser válido");
        }
    }

    /**
     * Teste 3.4: Validação de plano
     */
    public function testValidationPlan()
    {
        $company = new Company([
            'name' => 'Empresa Teste',
            'tax_id' => '999999999',
            'plan' => 'premium_plus', // inválido
        ]);
        
        $this->assertFalse($company->validate(['plan']));
        
        // Planos válidos
        foreach (['basic', 'professional', 'enterprise'] as $plan) {
            $company->plan = $plan;
            $this->assertTrue($company->validate(['plan']), "Plan '$plan' deveria ser válido");
        }
    }

    /**
     * Teste 3.5: Busca de empresa por ID (integração BD)
     */
    public function testFindById()
    {
        $company = Company::findOne(1);
        
        $this->assertNotNull($company);
        $this->assertEquals('Empresa Teste', $company->name);
        $this->assertEquals('123456789', $company->tax_id);
        $this->assertEquals('active', $company->status);
    }
}
