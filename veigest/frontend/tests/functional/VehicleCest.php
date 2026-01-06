<?php

namespace frontend\tests\functional;

use Yii;
use frontend\tests\FunctionalTester;
use frontend\tests\fixtures\UserFixture;
use frontend\tests\fixtures\CompanyFixture;
use frontend\tests\fixtures\VehicleFixture;

/**
 * Vehicle Management Functional Test
 * 
 * RF-TT-002: Teste funcional de gestão de veículos
 * Testa as funcionalidades de visualização e gestão de veículos
 */
class VehicleCest
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
        ];
    }

    /**
     * Preparação: login como manager antes de cada teste
     */
    public function _before(FunctionalTester $I)
    {
        // Login como manager para ter acesso aos veículos
        $I->amOnPage(['site/login']);
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('button[type="submit"]');
    }

    /**
     * Teste 1: Acesso à lista de veículos
     * Verifica que a página de veículos está acessível após login
     */
    public function testVehicleListAccessible(FunctionalTester $I)
    {
        $I->amOnPage(['vehicle/index']);
        
        // Deve ver a página de veículos
        $I->see('Veículos');
        // Deve ver dados da fixture
        $I->see('AA-00-AA'); // Matrícula do veículo 1
    }

    /**
     * Teste 2: Visualizar detalhes de um veículo
     * Verifica que é possível ver os detalhes de um veículo específico
     */
    public function testViewVehicleDetails(FunctionalTester $I)
    {
        $I->amOnPage(['vehicle/view', 'id' => 1]);
        
        // Deve ver detalhes do veículo
        $I->see('Volkswagen');
        $I->see('Golf');
        $I->see('AA-00-AA');
    }

    /**
     * Teste 3: Filtrar veículos por status
     * Verifica funcionalidade de filtro de veículos
     */
    public function testFilterVehiclesByStatus(FunctionalTester $I)
    {
        $I->amOnPage(['vehicle/index', 'status' => 'active']);
        
        // Deve ver veículos ativos
        $I->see('AA-00-AA'); // Veículo ativo
        $I->see('CC-22-CC'); // Veículo ativo (Tesla)
    }

    /**
     * Teste 4: Verificar informações de veículo em manutenção
     */
    public function testVehicleInMaintenance(FunctionalTester $I)
    {
        $I->amOnPage(['vehicle/view', 'id' => 2]); // Renault em manutenção
        
        $I->see('Renault');
        $I->see('Megane');
        $I->see('BB-11-BB');
    }

    /**
     * Teste 5: Acesso negado para usuário não logado
     */
    public function testVehicleAccessDeniedWithoutLogin(FunctionalTester $I)
    {
        // Fazer logout
        Yii::$app->user->logout();
        
        $I->amOnPage(['vehicle/index']);
        
        // Deve redirecionar para login
        $I->seeInCurrentUrl('login');
    }
}
