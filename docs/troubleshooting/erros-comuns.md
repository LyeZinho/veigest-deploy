# 🐛 Erros Comuns e Soluções

## Visão Geral

Este documento lista os erros mais comuns no VeiGest e como resolvê-los.

---

## Erros de Base de Dados

### PDOException: SQLSTATE[42S22] Column not found

**Erro:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'xxx' in 'field list'
```

**Causa:** Coluna não existe na tabela.

**Solução:**
1. Verificar se a migration foi aplicada:
   ```bash
   php yii migrate/history
   ```

2. Aplicar migrations pendentes:
   ```bash
   php yii migrate
   ```

3. Se necessário, criar migration para adicionar a coluna:
   ```bash
   php yii migrate/create add_xxx_column_to_table
   ```

---

### PDOException: SQLSTATE[42S02] Table not found

**Erro:**
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'veigest.xxx' doesn't exist
```

**Solução:**
1. Aplicar todas as migrations:
   ```bash
   php yii migrate --interactive=0
   ```

2. Verificar nome da tabela no Model:
   ```php
   public static function tableName()
   {
       return '{{%nome_correcto}}';  // Com prefixo
   }
   ```

---

### PDOException: SQLSTATE[23000] Duplicate entry

**Erro:**
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'xxx' for key 'yyy'
```

**Causa:** Tentativa de inserir valor duplicado em coluna UNIQUE.

**Solução:**
1. Verificar se o registo já existe antes de criar:
   ```php
   $exists = Model::find()->where(['campo_unico' => $valor])->exists();
   if (!$exists) {
       $model->save();
   }
   ```

2. Usar `INSERT ... ON DUPLICATE KEY UPDATE`:
   ```php
   Yii::$app->db->createCommand()
       ->upsert('tabela', $dados, $dadosActualizacao)
       ->execute();
   ```

---

### PDOException: SQLSTATE[HY000] General error: 1364 Field doesn't have a default value

**Erro:**
```
Field 'xxx' doesn't have a default value
```

**Solução:**
1. Definir valor default no Model:
   ```php
   public function rules()
   {
       return [
           [['campo'], 'default', 'value' => 'valor_default'],
       ];
   }
   ```

2. Ou na migration:
   ```php
   $this->addColumn('tabela', 'campo', 
       $this->string()->defaultValue('default'));
   ```

---

## Erros de Autenticação

### 401 Unauthorized - Token inválido

**Erro:**
```json
{"success": false, "message": "Token inválido ou expirado", "code": 401}
```

**Causa:** Token de API inválido ou mal formatado.

**Solução:**
1. Verificar formato do header:
   ```
   Authorization: Bearer {token}
   ```

2. Obter novo token:
   ```bash
   curl -X POST http://localhost/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"username":"admin","password":"admin123"}'
   ```

3. Verificar se o utilizador está activo (status = 10).

---

### 403 Forbidden - Acesso negado

**Erro:**
```json
{"success": false, "message": "Acesso negado", "code": 403}
```

**Causa:** Utilizador não tem permissões para o recurso.

**Solução:**
1. Verificar role do utilizador:
   ```php
   $role = Yii::$app->user->identity->role;
   ```

2. Verificar se pertence à mesma empresa:
   ```php
   $companyId = Yii::$app->user->identity->company_id;
   ```

---

### Login Falha - "Invalid username or password"

**Causa:** Credenciais incorrectas ou utilizador inactivo.

**Solução:**
1. Verificar se utilizador existe:
   ```bash
   php yii console/check-user admin
   ```

2. Verificar status do utilizador (deve ser 10):
   ```sql
   SELECT id, username, status FROM user WHERE username = 'admin';
   ```

3. Resetar password:
   ```php
   $user = User::findByUsername('admin');
   $user->setPassword('nova_password');
   $user->save();
   ```

---

## Erros de Validação

### 422 Unprocessable Entity - Validation Error

**Erro:**
```json
{
  "success": false,
  "message": "Erro de validação",
  "errors": {
    "campo": ["Campo não pode estar vazio"]
  }
}
```

**Solução:**
1. Verificar regras de validação no Model:
   ```php
   public function rules()
   {
       return [
           [['campo_obrigatorio'], 'required'],
       ];
   }
   ```

2. Enviar todos os campos obrigatórios na request.

---

### Erro: "XXX cannot be blank"

**Causa:** Campo obrigatório não enviado.

**Solução:**
1. Verificar campos obrigatórios:
   ```php
   // No model
   [['company_id', 'name'], 'required'],
   ```

2. Definir valores antes de guardar:
   ```php
   $model->company_id = Yii::$app->user->identity->company_id;
   $model->save();
   ```

---

## Erros de CSRF

### 400 Bad Request - Unable to verify your data submission

**Causa:** Token CSRF inválido ou ausente.

**Solução para Frontend:**
```php
<?php $form = ActiveForm::begin(); ?>
    <!-- CSRF token incluído automaticamente -->
<?php ActiveForm::end(); ?>
```

**Solução para API (desactivar CSRF):**
```php
// No controller
public function behaviors()
{
    $behaviors = parent::behaviors();
    
    // Desactivar CSRF para API
    $behaviors['authenticator']['except'] = ['options'];
    
    return $behaviors;
}
```

---

## Erros de Upload

### Erro: "Upload file is too large"

**Causa:** Ficheiro excede limite de upload.

**Solução:**
1. Verificar `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 12M
   ```

2. Ou em `config/main.php`:
   ```php
   'components' => [
       'request' => [
           'parsers' => [
               'multipart/form-data' => 'yii\web\MultipartFormDataParser',
           ],
       ],
   ],
   ```

---

### Erro: "Only files with these extensions are allowed"

**Causa:** Extensão de ficheiro não permitida.

**Solução:**
```php
// No model de upload
public function rules()
{
    return [
        [['file'], 'file', 
            'extensions' => 'pdf, doc, docx, jpg, png',
            'maxSize' => 10 * 1024 * 1024, // 10MB
        ],
    ];
}
```

---

## Erros de Rotas

### 404 Not Found

**Causa:** Rota não configurada.

**Solução:**
1. Verificar configuração de rotas em `config/main.php`:
   ```php
   'urlManager' => [
       'rules' => [
           'GET api/endpoint' => 'api/controller/action',
       ],
   ],
   ```

2. Verificar nome do controller e action:
   ```
   api/VehicleController.php → /api/vehicle/action
   ```

---

### Erro: "Invalid Route"

**Causa:** Controller ou action não existe.

**Solução:**
1. Verificar namespace do controller:
   ```php
   namespace backend\modules\api\controllers;
   ```

2. Verificar se o método action existe:
   ```php
   public function actionIndex() { ... }  // /api/xxx/index
   public function actionView($id) { ... } // /api/xxx/view?id=1
   ```

---

## Erros de JavaScript

### Chart.js: Canvas não encontrado

**Erro:**
```
Cannot read property 'getContext' of null
```

**Solução:**
```javascript
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('myChart');
    if (ctx) {
        new Chart(ctx, { ... });
    }
});
```

---

### Erro de CORS

**Erro:**
```
Access to fetch has been blocked by CORS policy
```

**Solução no backend:**
```php
// backend/modules/api/controllers/BaseApiController.php
public function behaviors()
{
    $behaviors = parent::behaviors();
    
    $behaviors['corsFilter'] = [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => ['http://localhost:3000'],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE'],
            'Access-Control-Request-Headers' => ['*'],
            'Access-Control-Allow-Credentials' => true,
        ],
    ];
    
    return $behaviors;
}
```

---

## Erros de Docker

### Container não inicia

**Erro:**
```
ERROR: for veigest_web Cannot start service web: driver failed
```

**Solução:**
```bash
# Parar containers
docker-compose down

# Limpar volumes
docker-compose down -v

# Rebuild
docker-compose build --no-cache

# Iniciar
docker-compose up -d
```

---

### Erro de permissões em runtime

**Erro:**
```
Unable to write to directory: /app/runtime
```

**Solução:**
```bash
# No container
docker-compose exec web chmod -R 777 runtime
docker-compose exec web chmod -R 777 web/assets
```

---

## Comandos de Diagnóstico

### Verificar logs

```bash
# Yii2 logs
tail -f runtime/logs/app.log

# Nginx logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php-fpm/error.log
```

### Verificar conexão à BD

```php
// check_db.php
try {
    $db = Yii::$app->db;
    $db->open();
    echo "Conexão OK: " . $db->dsn;
} catch (\Exception $e) {
    echo "Erro: " . $e->getMessage();
}
```

### Limpar cache

```bash
# Cache do Yii
php yii cache/flush-all

# Assets
rm -rf frontend/web/assets/*
rm -rf backend/web/assets/*
```

---

## Próximos Passos

- [Técnicas de Debug](debug.md)
- [Guia de Testes](../guias/testes.md)
