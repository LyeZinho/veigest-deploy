<?php

namespace backend\tests\functional;

use Yii;
use backend\tests\FunctionalTester;
use backend\tests\fixtures\UserFixture;
use backend\tests\fixtures\CompanyFixture;
use backend\tests\fixtures\AuthAssignmentFixture;
use common\models\User;

/**
 * Teste Funcional #3: CompanyManagementCest
 * 
 * RF-TT-002: Teste funcional de gestão de empresas
 * Testa as funcionalidades de gestão de empresas no backend
 */
class CompanyManagementCest
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
     * Teste 3.1: Lista de empresas acessível para admin
     */
    public function testCompanyListAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('company/index');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 3.2: Visualização de detalhes da empresa
     */
    public function testCompanyViewAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('company/view', ['id' => 1]);
        $I->seeResponseCodeIs(200);
        $I->see('Empresa Teste');
    }

    /**
     * Teste 3.3: Página de criação de empresa acessível
     */
    public function testCompanyCreatePageAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('company/create');
        $I->seeResponseCodeIs(200);
    }

    /**
     * Teste 3.4: Página de edição de empresa acessível
     */
    public function testCompanyUpdatePageAccessible(FunctionalTester $I)
    {
        $I->amOnRoute('company/update', ['id' => 1]);
        $I->seeResponseCodeIs(200);
    }
}
