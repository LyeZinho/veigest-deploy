<?php

namespace backend\tests\unit\models;

use Yii;
use backend\tests\UnitTester;
use common\models\User;
use backend\tests\fixtures\UserFixture;
use backend\tests\fixtures\CompanyFixture;

/**
 * Teste Unitário #1: UserTest
 * 
 * RF-TT-001: Testes unitários para o modelo User
 * - Validação de parâmetros de entrada
 * - Integração com Base de Dados (Active Record)
 */
class UserTest extends \Codeception\Test\Unit
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
     * Teste 1.1: Validação de campos obrigatórios
     * Verifica se o modelo rejeita dados inválidos
     */
    public function testValidationRulesRequired()
    {
        $user = new User();
        
        // Modelo vazio não deve validar
        $this->assertFalse($user->validate());
        
        // Deve ter erros nos campos obrigatórios
        $this->assertArrayHasKey('username', $user->errors);
        $this->assertArrayHasKey('name', $user->errors);
        $this->assertArrayHasKey('email', $user->errors);
        $this->assertArrayHasKey('company_id', $user->errors);
    }

    /**
     * Teste 1.2: Validação de email
     * Verifica se o modelo valida corretamente o formato de email
     */
    public function testValidationEmail()
    {
        $user = new User([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'email-invalido',
            'company_id' => 1,
        ]);
        
        $this->assertFalse($user->validate(['email']));
        $this->assertArrayHasKey('email', $user->errors);
        
        // Email válido deve passar
        $user->email = 'email@valido.com';
        $this->assertTrue($user->validate(['email']));
    }

    /**
     * Teste 1.3: Validação de status
     * Verifica se o modelo aceita apenas status válidos
     */
    public function testValidationStatus()
    {
        $user = new User([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@test.com',
            'company_id' => 1,
            'status' => 'invalid_status',
        ]);
        
        $this->assertFalse($user->validate(['status']));
        
        // Status válidos
        foreach (['active', 'inactive'] as $validStatus) {
            $user->status = $validStatus;
            $this->assertTrue($user->validate(['status']), "Status '$validStatus' deveria ser válido");
        }
    }

    /**
     * Teste 1.4: findByUsername (integração BD)
     * Verifica a busca de utilizador por username
     */
    public function testFindByUsername()
    {
        // Utilizador existente
        $user = User::findByUsername('admin');
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->username);
        
        // Utilizador inexistente
        $notFound = User::findByUsername('usuario_inexistente');
        $this->assertNull($notFound);
    }

    /**
     * Teste 1.5: validatePassword
     * Verifica a validação de password
     */
    public function testValidatePassword()
    {
        $user = User::findByUsername('admin');
        $this->assertNotNull($user);
        
        // Password correta
        $this->assertTrue($user->validatePassword('admin'));
        
        // Password incorreta
        $this->assertFalse($user->validatePassword('senha_errada'));
        
        // Password vazia lança exceção - verificar que não aceita
        $this->expectException(\yii\base\InvalidArgumentException::class);
        $user->validatePassword('');
    }
}
