# RELATÓRIO TÉCNICO DETALHADO - MIGRAÇÕES E CORREÇÕES
**Data:** 19 de novembro de 2025  
**Sessão:** Configuração VeiGest + Resolução de Problemas de Login  

---

## 🔍 ANÁLISE TÉCNICA DOS PROBLEMAS ENCONTRADOS

### **Problema 1: Tabela User Não Encontrada**
```
Error: The table does not exist: {{%users}}
```

**Análise da Causa Raiz:**
- Migração `m130524_201442_init.php` cria tabela como `{{%user}}` (singular)
- Modelo `User.php` estava configurado para `{{%users}}` (plural)
- Yii2 ActiveRecord não conseguia mapear o modelo para a tabela

**Código Problemático:**
```php
// User.php - ANTES (Incorreto)
public static function tableName()
{
    return '{{%users}}';  // Plural - ERRO
}
```

**Correção Aplicada:**
```php
// User.php - DEPOIS (Correto)
public static function tableName()
{
    return '{{%user}}';   // Singular - CORRETO
}
```

### **Problema 2: Erro CSRF (Bad Request #400)**
```
Bad Request (#400): Unable to verify your data submission
```

**Análise da Causa Raiz:**
- Configuração CSRF válida mas possível conflito com sessões
- Cookie validation key configurada corretamente
- Problema possivelmente relacionado com cache ou headers HTTP

**Diagnóstico Executado:**
```php
// Verificação realizada
$request = Yii::$app->request;
echo "CSRF Param: " . $request->csrfParam; // _csrf-frontend
echo "Cookie Key: " . $request->cookieValidationKey; // OK
$csrfToken = $request->getCsrfToken(); // Gerado com sucesso
```

**Ações Corretivas:**
1. Limpeza completa do cache (`frontend/runtime/cache/*`)
2. Teste temporário com `enableCsrfValidation => false`
3. Verificação de configurações de sessão
4. Restauração da proteção CSRF após correção do problema principal

### **Problema 3: Autenticação Falhando**
```
Incorrect username or password.
```

**Análise da Causa Raiz:**
- Hash da password estava correto (`admin` = hash válido)
- Problema no método `findByUsername()` do modelo User
- Método procurava no campo errado da base de dados

**Código Problemático:**
```php
// LoginForm.php chama User::findByUsername()
public static function findByUsername($nome) // Parâmetro confuso
{
    return static::findOne(['nome' => $nome, 'status' => self::STATUS_ACTIVE]);
    //                      ^^^^^^ CAMPO ERRADO
}
```

**Análise da Base de Dados:**
- Campo `username` existe e contém 'admin'
- Campo `nome` contém 'VeiGest Admin'  
- Login usa campo `username` mas busca era em `nome`

**Correção Crítica:**
```php
// ANTES - Busca incorreta
public static function findByUsername($nome) {
    return static::findOne(['nome' => $nome, 'status' => self::STATUS_ACTIVE]);
}

// DEPOIS - Busca correta  
public static function findByUsername($username) {
    return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
}
```

---

## 🛠️ FERRAMENTAS E SCRIPTS CRIADOS

### **1. Script de Gestão de Utilizadores**
**Arquivo:** `console/controllers/UserController.php`
```php
// Funcionalidades implementadas
public function actionCreate($username, $email, $password) // Criar utilizador
// Verifica empresa existente, cria utilizador com hash seguro
```

### **2. Script de Gestão de Passwords**
**Arquivo:** `console/controllers/PasswordController.php`
```php
public function actionReset($username, $newPassword) // Reset password
public function actionInfo($username) // Info do utilizador
```

### **3. Script de Atualização Direta de Password**
**Arquivo:** `update_password.php`
```php
// Atualização direta na BD usando Yii::$app->security
$newPasswordHash = Yii::$app->security->generatePasswordHash('admin');
// Execução: UPDATE user SET password_hash = ? WHERE username = 'admin'
```

### **4. Script de Teste CSRF**
**Arquivo:** `test_csrf.php`
```php
// Diagnóstico completo de configuração CSRF
// Verificação de componentes, tokens, validação
```

### **5. Script de Correção Temporária**
**Arquivo:** `fix_csrf.php`
```php
// Desabilitação temporária CSRF para isolamento de problema
// Modificação dinâmica de main.php
```

---

## 📊 MÉTRICAS DE PERFORMANCE

### **Base de Dados - Migrações Executadas:**
```
*** applying m130524_201442_init (time: 0.069s)
*** applied m190124_110200_add_verification_token_column_to_user_table (time: 0.123s)
*** applied m251118_000001_create_companies_table (time: 0.443s)
*** applied m251118_000002_create_rbac_tables (time: 0.597s)
*** applied m251118_000003_create_users_table (time: 0.728s)
*** applied m251118_000004_create_files_table (time: 0.220s)
*** applied m251118_000005_create_vehicles_table (time: 0.362s)
*** applied m251118_000006_create_maintenances_table (time: 0.407s)
*** applied m251118_000007_create_documents_table (time: 0.631s)
*** applied m251118_000008_create_fuel_logs_table (time: 0.363s)
*** applied m251118_000009_create_alerts_table (time: 0.176s)
*** applied m251118_000010_create_activity_logs_table (time: 0.259s)
*** applied m251118_000011_create_views (time: 0.039s)
*** applied m251118_000012_insert_rbac_data (time: 0.021s)
*** applied m251118_000013_assign_rbac_permissions (time: 0.036s)

Total: 15 migrations applied successfully
```

### **Testes de Conectividade:**
```
Frontend: http://localhost -> Status: 200 ✅
Backend: http://localhost:8080 -> Status: 200 ✅
Login: admin/admin -> Authentication: SUCCESS ✅
```

---

## 🔐 ANÁLISE DE SEGURANÇA

### **Hash de Passwords:**
- **Algoritmo:** bcrypt (Yii2 default)
- **Exemplo:** `$2y$13$diW.T/3DCqDUQ3uZ9P5aQOt...`
- **Força:** Cost factor 13 (alta segurança)
- **Validação:** Via `password_verify()` do PHP

### **Proteção CSRF:**
- **Token:** Geração automática por sessão
- **Parâmetro:** `_csrf-frontend`
- **Validação:** Ativa em todas as requisições POST
- **Cookie Key:** `IA2NKOa49ZMWDomMLOQv9s7nordIMSNL`

### **Configuração de Sessões:**
```php
'session' => [
    'name' => 'advanced-frontend',    // Nome único da sessão
],
'user' => [
    'identityClass' => 'common\models\User',
    'enableAutoLogin' => true,        // Remember me
    'identityCookie' => [
        'name' => '_identity-frontend', 
        'httpOnly' => true            // Proteção XSS
    ],
],
```

---

## 📋 CHECKLIST DE VALIDAÇÃO FINAL

### ✅ **Infraestrutura:**
- [x] Nginx 1.29.3 instalado e configurado
- [x] PHP-FPM 8.4 ativo na porta 9000
- [x] MySQL 9.1.0 com base dados veigest
- [x] Yii2 Advanced Template funcional

### ✅ **Base de Dados:**
- [x] 15 migrações aplicadas com sucesso
- [x] Estrutura completa VeiGest criada
- [x] Sistema RBAC implementado
- [x] Utilizador admin configurado

### ✅ **Autenticação:**
- [x] Login frontend funcional
- [x] Login backend funcional  
- [x] Hash de passwords seguro
- [x] Proteção CSRF ativa

### ✅ **Testes:**
- [x] Conectividade HTTP (200 OK)
- [x] Autenticação (admin/admin)
- [x] Navegação básica
- [x] Formulários funcionais

---

## 🎯 LIÇÕES APRENDIDAS

### **1. Consistência de Nomenclatura:**
- Manter consistência entre modelos e migrações
- Usar sempre convenções do framework (singular/plural)
- Documentar claramente mapeamentos de campos

### **2. Debug Sistemático:**
- Isolar problemas um de cada vez
- Usar ferramentas de diagnóstico específicas
- Manter logs detalhados de alterações

### **3. Gestão de Estado:**
- Cache pode mascarar problemas
- Sessões afetam autenticação
- Limpeza regular de runtime necessária

### **4. Segurança em Desenvolvimento:**
- Nunca desabilitar CSRF em produção
- Testar sempre com proteções ativadas
- Validar hashes de password regularmente

---

**Relatório técnico gerado em:** 19/11/2025 às 15:50  
**Status final:** ✅ TODOS OS PROBLEMAS RESOLVIDOS  
**Sistema:** 100% OPERACIONAL