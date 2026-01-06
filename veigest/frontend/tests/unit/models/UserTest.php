<?php

namespace frontend\tests\unit\models;

use Yii;
use common\models\User;
use frontend\tests\fixtures\UserFixture;
use frontend\tests\fixtures\CompanyFixture;
use Codeception\Test\Unit;

/**
 * User Model Unit Test
 * 
 * RF-TT-001: Testes unitários para modelo User
 * Testa validações, autenticação e métodos do modelo
 * 
 * @property \frontend\tests\UnitTester $tester
 */
class UserTest extends Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;

    /**
     * Load fixtures before each test
     */
    public function _fixtures()
    {
        return [
            'company' => [
                'class' => CompanyFixture::class,
            ],
            'user' => [
                'class' => UserFixture::class,
            ],
        ];
    }

    /**
     * Teste 1: Validação de campos obrigatórios
     * Verifica que username, name, email e company_id são obrigatórios
     */
    public function testRequiredFieldsValidation()
    {
        $user = new User();
        
        // Model sem dados não deve validar
        $this->assertFalse($user->validate());
        
        // Verificar erros de campos obrigatórios
        $this->assertArrayHasKey('username', $user->errors);
        $this->assertArrayHasKey('name', $user->errors);
        $this->assertArrayHasKey('email', $user->errors);
        $this->assertArrayHasKey('company_id', $user->errors);
    }

    /**
     * Teste 2: Validação de formato de email
     * Verifica que email deve ter formato válido
     */
    public function testEmailFormatValidation()
    {
        $user = new User([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'invalid-email-format', // Email inválido
            'company_id' => 1,
        ]);
        
        $this->assertFalse($user->validate(['email']));
        $this->assertArrayHasKey('email', $user->errors);
        
        // Email válido deve passar
        $user->email = 'valid@example.com';
        $this->assertTrue($user->validate(['email']));
    }

    /**
     * Teste 3: Validação de roles permitidos
     * Verifica que role deve estar entre admin, manager, driver
     */
    public function testRoleValidation()
    {
        $user = new User([
            'username' => 'roletest',
            'name' => 'Role Test User',
            'email' => 'role@test.com',
            'company_id' => 1,
        ]);
        
        // Role inválido
        $user->role = 'invalid_role';
        $this->assertFalse($user->validate(['role']));
        
        // Roles válidos
        $validRoles = ['admin', 'manager', 'driver'];
        foreach ($validRoles as $role) {
            $user->role = $role;
            $this->assertTrue($user->validate(['role']), "Role '$role' deveria ser válido");
        }
    }

    /**
     * Teste 4: Verificação de password hash
     * Testa setPassword e validatePassword
     */
    public function testPasswordHashing()
    {
        $user = new User();
        $plainPassword = 'mySecurePassword123';
        
        // Definir password
        $user->setPassword($plainPassword);
        
        // Hash deve ser gerado
        $this->assertNotEmpty($user->password_hash);
        $this->assertNotEquals($plainPassword, $user->password_hash);
        
        // Validar password correto
        $this->assertTrue($user->validatePassword($plainPassword));
        
        // Validar password incorreto
        $this->assertFalse($user->validatePassword('wrongPassword'));
    }

    /**
     * Teste 5: Busca de usuário por username
     * Verifica método findByUsername
     */
    public function testFindByUsername()
    {
        // Buscar admin (existe na fixture)
        $user = User::findByUsername('admin');
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->username);
        $this->assertEquals('active', $user->status);
        
        // Buscar usuário inexistente
        $notFound = User::findByUsername('nonexistent_user_12345');
        $this->assertNull($notFound);
    }

    /**
     * Teste 6: Validação de status
     * Verifica que status deve estar entre active e inactive
     */
    public function testStatusValidation()
    {
        $user = new User([
            'username' => 'statustest',
            'name' => 'Status Test',
            'email' => 'status@test.com',
            'company_id' => 1,
        ]);
        
        // Status inválido
        $user->status = 'invalid_status';
        $this->assertFalse($user->validate(['status']));
        
        // Status válidos
        $user->status = 'active';
        $this->assertTrue($user->validate(['status']));
        
        $user->status = 'inactive';
        $this->assertTrue($user->validate(['status']));
    }

    /**
     * Teste 7: Unicidade de username
     * Verifica que username deve ser único
     */
    public function testUsernameUniqueness()
    {
        // Tentar criar usuário com username já existente
        $user = new User([
            'username' => 'admin', // Já existe na fixture
            'name' => 'Duplicate Admin',
            'email' => 'duplicate@test.com',
            'company_id' => 1,
        ]);
        
        $this->assertFalse($user->validate(['username']));
        $this->assertArrayHasKey('username', $user->errors);
    }
}
