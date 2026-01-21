<?php

namespace backend\tests\functional;

use Yii;
use backend\tests\FunctionalTester;
use backend\tests\fixtures\UserFixture;
use backend\tests\fixtures\CompanyFixture;
use backend\tests\fixtures\AuthAssignmentFixture;
use common\models\User;

/**
 * Teste Funcional #4: DashboardCest
 * 
 * RF-TT-002: Teste funcional do Dashboard do backend
 * Testa o acesso e funcionalidades do dashboard administrativo
 */
class DashboardCest
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
     * Teste 4.1: Dashboard acessível para admin
     */
    public function testDashboardAccessibleForAdmin(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findByUsername('admin'));
        $I->amOnRoute('site/index');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 4.2: Dashboard NÃO acessível para manager (backend é apenas admin)
     */
    public function testDashboardNotAccessibleForManager(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findByUsername('manager'));
        $I->amOnRoute('site/index');
        $I->seeResponseCodeIs(403);
    }

    /**
     * Teste 4.3: Dashboard NÃO acessível para driver (403)
     */
    public function testDashboardNotAccessibleForDriver(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findByUsername('driver1'));
        $I->amOnRoute('site/index');
        $I->seeResponseCodeIs(403);
    }

    /**
     * Teste 4.4: Utilizador não autenticado é redirecionado para login
     */
    public function testUnauthenticatedUserRedirectedToLogin(FunctionalTester $I)
    {
        // Limpar sessão
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        
        $I->amOnRoute('site/index');
        // Deve ser redirecionado para login
        $I->seeInCurrentUrl('login');
    }
}
