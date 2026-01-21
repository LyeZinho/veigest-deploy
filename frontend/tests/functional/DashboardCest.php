<?php

namespace frontend\tests\functional;

use Yii;
use frontend\tests\FunctionalTester;
use frontend\tests\fixtures\UserFixture;
use frontend\tests\fixtures\CompanyFixture;

/**
 * Dashboard Functional Test
 * 
 * RF-TT-002: Teste funcional do Dashboard principal
 * Testa acesso e funcionalidades do dashboard após login
 */
class DashboardCest
{
    /**
     * Load fixtures before tests
     */
    public function _fixtures()
    {
        return [
            'company' => CompanyFixture::class,
            'user' => UserFixture::class,
        ];
    }

    /**
     * Teste 1: Dashboard acessível após login como Manager
     */
    public function testDashboardAccessibleForManager(FunctionalTester $I)
    {
        // Login como manager
        $I->amOnPage(['site/login']);
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('button[type="submit"]');
        
        // Aceder ao dashboard
        $I->amOnPage(['dashboard/index']);
        
        // Deve ver o dashboard
        $I->see('Dashboard');
    }

    /**
     * Teste 2: Dashboard acessível após login como Driver
     */
    public function testDashboardAccessibleForDriver(FunctionalTester $I)
    {
        // Login como driver
        $I->amOnPage(['site/login']);
        $I->fillField('LoginForm[username]', 'driver1');
        $I->fillField('LoginForm[password]', 'driver123');
        $I->click('button[type="submit"]');
        
        // Aceder ao dashboard
        $I->amOnPage(['dashboard/index']);
        
        // Deve ver o dashboard (versão driver)
        $I->see('Dashboard');
    }

    /**
     * Teste 3: Dashboard não acessível sem login
     */
    public function testDashboardNotAccessibleWithoutLogin(FunctionalTester $I)
    {
        // Tentar aceder ao dashboard sem login
        $I->amOnPage(['dashboard/index']);
        
        // Deve redirecionar para login
        $I->seeInCurrentUrl('login');
    }

    /**
     * Teste 4: Verificar menu de navegação para Manager
     */
    public function testNavigationMenuForManager(FunctionalTester $I)
    {
        // Login como manager
        $I->amOnPage(['site/login']);
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('button[type="submit"]');
        
        $I->amOnPage(['dashboard/index']);
        
        // Manager deve ver menu completo
        $I->see('Veículos');
        $I->see('Manutenções');
    }

    /**
     * Teste 5: Logout funcional
     */
    public function testLogoutFunctionality(FunctionalTester $I)
    {
        // Login
        $I->amOnPage(['site/login']);
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('button[type="submit"]');
        
        // Fazer logout
        $I->amOnPage(['site/logout']);
        
        // Tentar aceder ao dashboard
        $I->amOnPage(['dashboard/index']);
        
        // Deve redirecionar para login
        $I->seeInCurrentUrl('login');
    }
}
