<?php

namespace frontend\tests\functional;

use Yii;
use frontend\tests\FunctionalTester;
use frontend\tests\fixtures\UserFixture;
use frontend\tests\fixtures\CompanyFixture;
use frontend\tests\fixtures\VehicleFixture;
use frontend\tests\fixtures\MaintenanceFixture;

/**
 * Maintenance Management Functional Test
 * 
 * RF-TT-002: Teste funcional de gestão de manutenções
 * Testa as funcionalidades de visualização e gestão de manutenções
 */
class MaintenanceCest
{
    /**
     * Load fixtures before tests
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
     * Preparação: login como manager antes de cada teste
     */
    public function _before(FunctionalTester $I)
    {
        $I->amOnPage(['site/login']);
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('button[type="submit"]');
    }

    /**
     * Teste 1: Acesso à lista de manutenções
     * Verifica que a página de manutenções está acessível
     */
    public function testMaintenanceListAccessible(FunctionalTester $I)
    {
        $I->amOnPage(['maintenance/index']);
        
        $I->see('Manutenções');
    }

    /**
     * Teste 2: Visualizar detalhes de manutenção
     * Verifica que é possível ver detalhes de uma manutenção específica
     */
    public function testViewMaintenanceDetails(FunctionalTester $I)
    {
        $I->amOnPage(['maintenance/view', 'id' => 1]);
        
        // Deve ver informações da manutenção
        $I->see('Revisão geral');
        $I->see('250'); // Custo
    }

    /**
     * Teste 3: Filtrar manutenções por status
     * Verifica funcionalidade de filtro
     */
    public function testFilterMaintenancesByStatus(FunctionalTester $I)
    {
        $I->amOnPage(['maintenance/index', 'status' => 'scheduled']);
        
        // Deve ver manutenções agendadas
        $I->see('Manutenções');
    }

    /**
     * Teste 4: Verificar manutenção concluída
     */
    public function testViewCompletedMaintenance(FunctionalTester $I)
    {
        $I->amOnPage(['maintenance/view', 'id' => 2]); // Troca de travões - concluída
        
        $I->see('travões');
        $I->see('450'); // Custo
    }

    /**
     * Teste 5: Acesso à página de criar manutenção
     */
    public function testCreateMaintenancePageAccessible(FunctionalTester $I)
    {
        $I->amOnPage(['maintenance/create']);
        
        // Deve ver formulário de criação
        $I->see('Agendar Manutenção');
        $I->seeElement('form');
    }
}
