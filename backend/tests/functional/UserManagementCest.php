<?php

namespace backend\tests\functional;

use Yii;
use backend\tests\FunctionalTester;
use backend\tests\fixtures\UserFixture;
use backend\tests\fixtures\CompanyFixture;
use backend\tests\fixtures\AuthAssignmentFixture;
use common\models\User;

/**
 * Teste Funcional #2: UserManagementCest
 * 
 * RF-TT-002: Teste funcional de gestão de utilizadores
 * Testa as funcionalidades de visualização de utilizadores no backend
 */
class UserManagementCest
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
     * Login como admin antes de cada teste
     */
    public function _before(FunctionalTester $I)
    {
        // Login como admin para acessar backend
        $I->amLoggedInAs(User::findByUsername('admin'));
    }

    /**
     * Teste 2.1: Lista de utilizadores acessível para admin
     */
    public function testUserListAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('user/index');
        $I->seeResponseCodeIs(200);
        $I->see('admin');
    }

    /**
     * Teste 2.2: Visualização de detalhes do utilizador
     */
    public function testUserViewAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('user/view', ['id' => 1]);
        $I->seeResponseCodeIs(200);
        $I->see('admin');
    }

    /**
     * Teste 2.3: Página de criação de utilizador acessível
     */
    public function testUserCreatePageAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('user/create');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 2.4: Página de edição de utilizador acessível
     */
    public function testUserUpdatePageAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('user/update', ['id' => 1]);
        $I->seeResponseCodeIs(200);
    }
}
