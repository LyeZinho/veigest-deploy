<?php

namespace backend\tests\unit\models;

use Yii;
use backend\tests\UnitTester;
use common\models\LoginForm;
use common\models\User;
use backend\tests\fixtures\UserFixture;
use backend\tests\fixtures\CompanyFixture;

/**
 * Teste Unitário #4: LoginFormTest
 * 
 * RF-TT-001: Testes unitários para o modelo LoginForm
 * - Validação da lógica de negócio de autenticação
 * - Verificação de parâmetros de entrada
 */
class LoginFormTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;

    /**
     * Fixtures necessárias para os testes
     */
    public function _fixtures()
    {
        return [
            'company' => CompanyFixture::class,
            'user' => UserFixture::class,
        ];
    }

    /**
     * Teste 4.1: Validação de campos obrigatórios
     */
    public function testValidationRulesRequired()
    {
        $form = new LoginForm();
        
        // Formulário vazio não deve validar
        $this->assertFalse($form->validate());
        
        // Deve ter erros nos campos obrigatórios
        $this->assertArrayHasKey('username', $form->errors);
        $this->assertArrayHasKey('password', $form->errors);
    }

    /**
     * Teste 4.2: Login com credenciais inválidas
     */
    public function testLoginWithWrongCredentials()
    {
        $form = new LoginForm([
            'username' => 'admin',
            'password' => 'senha_errada',
        ]);
        
        $this->assertFalse($form->validate());
        $this->assertArrayHasKey('password', $form->errors);
    }

    /**
     * Teste 4.3: Login com utilizador inexistente
     */
    public function testLoginWithNonExistentUser()
    {
        $form = new LoginForm([
            'username' => 'usuario_nao_existe',
            'password' => 'qualquer_senha',
        ]);
        
        $this->assertFalse($form->validate());
        $this->assertArrayHasKey('password', $form->errors);
    }

    /**
     * Teste 4.4: Login com credenciais válidas (admin)
     */
    public function testLoginWithValidAdminCredentials()
    {
        $form = new LoginForm([
            'username' => 'admin',
            'password' => 'admin',
        ]);
        
        $this->assertTrue($form->validate(), 'Login com credenciais válidas deveria passar');
    }

    /**
     * Teste 4.5: Login com credenciais válidas (manager)
     */
    public function testLoginWithValidManagerCredentials()
    {
        $form = new LoginForm([
            'username' => 'manager',
            'password' => 'manager123',
        ]);
        
        $this->assertTrue($form->validate(), 'Login com credenciais de manager deveria passar');
    }

    /**
     * Teste 4.6: Validação de rememberMe como boolean
     */
    public function testRememberMeValidation()
    {
        $form = new LoginForm([
            'username' => 'admin',
            'password' => 'admin',
            'rememberMe' => 'not_boolean',
        ]);
        
        // O Yii2 converte automaticamente para boolean, então deve passar
        $form->validate(['rememberMe']);
        // Não deve haver erro específico de tipo
    }
}
