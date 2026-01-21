<?php

namespace frontend\tests\functional;

use Yii;
use frontend\tests\FunctionalTester;
use common\models\User;
use frontend\tests\fixtures\UserFixture;
use frontend\tests\fixtures\CompanyFixture;

/**
 * Frontend Login Functional Test (Cest format)
 * 
 * RF-TT-002: Teste funcional de login no frontend
 * Testa o fluxo completo de autenticação na área de utilizadores
 */
class LoginCest
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
     * Preparação antes de cada teste
     */
    public function _before(FunctionalTester $I)
    {
        // Garantir que não há usuário logado
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
    }

    /**
     * Teste 1: Acesso à página de login
     * Verifica que a página de login está acessível
     */
    public function testLoginPageAccessible(FunctionalTester $I)
    {
        $I->amOnPage(['site/login']);
        $I->see('Login');
        $I->seeElement('input[name="LoginForm[username]"]');
        $I->seeElement('input[name="LoginForm[password]"]');
    }

    /**
     * Teste 2: Login com credenciais inválidas
     * Verifica mensagem de erro para usuário/senha incorretos
     */
    public function testLoginWithInvalidCredentials(FunctionalTester $I)
    {
        $I->amOnPage(['site/login']);
        
        $I->fillField('LoginForm[username]', 'invalid_user');
        $I->fillField('LoginForm[password]', 'invalid_password');
        $I->click('button[type="submit"]');
        
        // Deve mostrar erro
        $I->see('Incorrect username or password');
        $I->seeInCurrentUrl('login');
    }

    /**
     * Teste 3: Login bem sucedido com manager
     * Verifica fluxo completo de login com gestor
     */
    public function testSuccessfulManagerLogin(FunctionalTester $I)
    {
        $I->amOnPage(['site/login']);
        
        $I->fillField('LoginForm[username]', 'manager');
        $I->fillField('LoginForm[password]', 'manager123');
        $I->click('button[type="submit"]');
        
        // Deve redirecionar para dashboard
        $I->dontSeeInCurrentUrl('login');
    }

    /**
     * Teste 4: Login bem sucedido com driver
     * Verifica fluxo de login com condutor
     */
    public function testSuccessfulDriverLogin(FunctionalTester $I)
    {
        $I->amOnPage(['site/login']);
        
        $I->fillField('LoginForm[username]', 'driver1');
        $I->fillField('LoginForm[password]', 'driver123');
        $I->click('button[type="submit"]');
        
        // Deve redirecionar para dashboard do condutor
        $I->dontSeeInCurrentUrl('login');
    }

    /**
     * Teste 5: Campos obrigatórios no formulário de login
     * Verifica validação de campos vazios
     */
    public function testLoginRequiredFields(FunctionalTester $I)
    {
        $I->amOnPage(['site/login']);
        
        // Submeter formulário vazio
        $I->click('button[type="submit"]');
        
        // Deve mostrar erros de validação
        $I->see('cannot be blank');
    }
}
