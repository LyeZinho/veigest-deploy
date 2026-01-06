<?php

namespace backend\tests\functional;

use Yii;
use backend\tests\FunctionalTester;
use backend\tests\fixtures\UserFixture;
use backend\tests\fixtures\CompanyFixture;
use backend\tests\fixtures\AuthAssignmentFixture;

/**
 * Teste Funcional #1: LoginCest
 * 
 * RF-TT-002: Teste funcional de login no back-office
 * Testa o fluxo completo de autenticação na área administrativa
 */
class LoginCest
{
    /**
     * Fixtures necessárias para os testes
     */
    public function _fixtures()
    {
        return [
            'company' => CompanyFixture::class,
            'user' => UserFixture::class,
            'auth' => AuthAssignmentFixture::class,
        ];
    }

    /**
     * Limpar sessão antes de cada teste
     */
    public function _before(FunctionalTester $I)
    {
        // Garantir que não há utilizador logado
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
    }

    /**
     * Teste 1.1: Página de login acessível
     */
    public function testLoginPageAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
        $I->seeResponseCodeIs(200);
        $I->see('Entrar'); // Página em português
    }

    /**
     * Teste 1.2: Login com credenciais inválidas
     */
    public function testLoginWithInvalidCredentials(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
        $I->fillField('LoginForm[username]', 'admin');
        $I->fillField('LoginForm[password]', 'senha_errada');
        $I->click('button[type=submit]');
        
        $I->seeResponseCodeIs(200);
        $I->see('Incorrect username or password');
    }

    /**
     * Teste 1.3: Login com credenciais válidas (admin)
     */
    public function testLoginWithValidAdminCredentials(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
        $I->fillField('LoginForm[username]', 'admin');
        $I->fillField('LoginForm[password]', 'admin');
        $I->click('button[type=submit]');
        
        // Após login bem sucedido, deve redirecionar para index
        $I->dontSee('Incorrect username or password');
    }

    /**
     * Teste 1.4: Login com utilizador inexistente
     */
    public function testLoginWithNonExistentUser(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
        $I->fillField('LoginForm[username]', 'usuario_nao_existe');
        $I->fillField('LoginForm[password]', 'qualquer_senha');
        $I->click('button[type=submit]');
        
        $I->seeResponseCodeIs(200);
        $I->see('Incorrect username or password');
    }

    /**
     * Teste 1.5: Campos obrigatórios vazios
     */
    public function testLoginWithEmptyFields(FunctionalTester $I)
    {
        $I->amOnRoute('site/login');
        $I->fillField('LoginForm[username]', '');
        $I->fillField('LoginForm[password]', '');
        $I->click('button[type=submit]');
        
        $I->seeResponseCodeIs(200);
        $I->see('cannot be blank');
    }
}
