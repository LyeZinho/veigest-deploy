<?php

namespace backend\tests\functional;

use Yii;
use backend\tests\FunctionalTester;
use backend\tests\fixtures\UserFixture;
use backend\tests\fixtures\CompanyFixture;
use backend\tests\fixtures\AuthAssignmentFixture;
use common\models\User;

/**
 * Teste Funcional #5: AccessControlCest
 * 
 * RF-TT-002: Teste funcional de controlo de acesso (RBAC)
 * Testa as permissões de acesso baseadas em roles
 */
class AccessControlCest
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
     * Teste 5.1: Admin pode acessar gestão de utilizadores
     */
    public function testAdminCanAccessUserManagement(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findByUsername('admin'));
        $I->amOnRoute('user/index');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 5.2: Admin pode acessar gestão de empresas
     */
    public function testAdminCanAccessCompanyManagement(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findByUsername('admin'));
        $I->amOnRoute('company/index');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 5.3: Driver NÃO pode acessar backend (403)
     */
    public function testDriverCannotAccessBackend(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findByUsername('driver1'));
        $I->amOnRoute('site/index');
        $I->seeResponseCodeIs(403);
    }

    /**
     * Teste 5.4: Página de erro acessível publicamente
     */
    public function testErrorPageAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('site/error');
        // Não deve retornar 403 (forbidden)
        $I->dontSeeResponseCodeIs(403);
    }

    /**
     * Teste 5.5: Página de login acessível publicamente
     */
    public function testLoginPageAlwaysAccessible(FunctionalTester $I)
    {
        // Garantir que não há utilizador logado
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        
        $I->amOnRoute('site/login');
        $I->seeResponseCodeIs(200);
    }
}
