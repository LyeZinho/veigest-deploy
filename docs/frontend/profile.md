# 👤 Sistema de Perfil de Utilizador

## Visão Geral

O sistema de perfil permite aos utilizadores gerir suas informações pessoais, incluindo dados de contacto, foto de perfil e palavra-passe. Todas as alterações são auditadas no histórico.

**Requisito Funcional:** RF-FO-003 - Gestão de Perfil Pessoal

---

## Funcionalidades Implementadas

| Código | Funcionalidade | Descrição |
|--------|----------------|-----------|
| RF-FO-003.1 | Visualização de dados pessoais | Ver todas as informações do perfil |
| RF-FO-003.2 | Edição de informações de contacto | Alterar nome, email, telefone |
| RF-FO-003.3 | Alteração de palavra-passe | Mudar senha com validações |
| RF-FO-003.4 | Upload de foto de perfil | Carregar/remover avatar |
| RF-FO-003.5 | Histórico de alterações | Auditoria completa de mudanças |

---

## Arquitetura

### Estrutura de Ficheiros

```
frontend/
├── controllers/
│   └── ProfileController.php      # Controller principal
├── models/
│   ├── ProfileForm.php            # Form para edição de perfil
│   └── ChangePasswordForm.php     # Form para alteração de senha
├── views/profile/
│   ├── index.php                  # Visualização do perfil
│   ├── update.php                 # Edição de dados
│   ├── change-password.php        # Alteração de senha
│   └── history.php                # Histórico de alterações
└── web/uploads/avatars/           # Diretório de fotos

common/
└── models/
    └── ProfileHistory.php         # Model de histórico
    
console/migrations/
└── m251125_010000_create_profile_history_table.php
```

### Diagrama de Fluxo

```
┌─────────────┐     ┌─────────────────┐     ┌─────────────┐
│   Browser   │────▶│ProfileController│────▶│    View     │
└─────────────┘     └────────┬────────┘     └─────────────┘
                             │
              ┌──────────────┼──────────────┐
              ▼              ▼              ▼
       ┌───────────┐  ┌───────────┐  ┌───────────┐
       │ProfileForm│  │ChangePass │  │  Profile  │
       │           │  │   Form    │  │  History  │
       └─────┬─────┘  └─────┬─────┘  └─────┬─────┘
             │              │              │
             └──────────────┴──────────────┘
                            │
                            ▼
                     ┌─────────────┐
                     │    User     │
                     │   (Model)   │
                     └─────────────┘
```

---

## ProfileController

### Rotas Disponíveis

| Rota | Action | Descrição |
|------|--------|-----------|
| `/profile` | `index` | Visualizar perfil |
| `/profile/update` | `update` | Editar informações |
| `/profile/change-password` | `changePassword` | Alterar senha |
| `/profile/history` | `history` | Ver histórico |
| `/profile/delete-photo` | `deletePhoto` | Remover foto (POST) |

### Código do Controller

```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\data\ActiveDataProvider;
use frontend\models\ProfileForm;
use frontend\models\ChangePasswordForm;
use common\models\ProfileHistory;

class ProfileController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Apenas autenticados
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-photo' => ['POST'],
                ],
            ],
        ];
    }

    // RF-FO-003.1: Visualização
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        
        $historyProvider = new ActiveDataProvider([
            'query' => ProfileHistory::find()
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', [
            'user' => $user,
            'historyProvider' => $historyProvider,
        ]);
    }

    // RF-FO-003.2 + RF-FO-003.4: Edição + Upload
    public function actionUpdate()
    {
        $model = new ProfileForm();
        $model->loadFromUser(Yii::$app->user->identity);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->photoFile = UploadedFile::getInstance($model, 'photoFile');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Perfil atualizado.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    // RF-FO-003.3: Alteração de senha
    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Senha alterada.');
            return $this->redirect(['index']);
        }

        return $this->render('change-password', ['model' => $model]);
    }

    // RF-FO-003.5: Histórico
    public function actionHistory()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ProfileHistory::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('history', ['dataProvider' => $dataProvider]);
    }
}
```

---

## Models

### ProfileForm

Formulário para edição de dados pessoais.

```php
<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use common\models\User;
use common\models\ProfileHistory;

class ProfileForm extends Model
{
    public $name;
    public $email;
    public $phone;
    public $license_number;
    public $license_expiry;
    
    /** @var UploadedFile */
    public $photoFile;

    private $_user;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
            ['email', 'validateUniqueEmail'],
            [['name'], 'string', 'max' => 150],
            [['phone'], 'string', 'max' => 20],
            [['license_number'], 'string', 'max' => 50],
            [['license_expiry'], 'date', 'format' => 'php:Y-m-d'],
            [['photoFile'], 'file', 
                'skipOnEmpty' => true, 
                'extensions' => 'png, jpg, jpeg, gif', 
                'maxSize' => 2 * 1024 * 1024, // 2MB
                'mimeTypes' => ['image/png', 'image/jpeg', 'image/gif'],
            ],
        ];
    }

    public function loadFromUser(User $user)
    {
        $this->_user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->license_number = $user->license_number;
        $this->license_expiry = $user->license_expiry;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        $changedFields = [];

        // Detecta campos alterados
        foreach (['name', 'email', 'phone', 'license_number', 'license_expiry'] as $field) {
            if ($user->$field !== $this->$field) {
                $changedFields[$field] = ['old' => $user->$field, 'new' => $this->$field];
                $user->$field = $this->$field;
            }
        }

        // Upload de foto
        if ($this->photoFile) {
            $photoPath = $this->uploadPhoto();
            if ($photoPath) {
                $changedFields['photo'] = ['old' => $user->photo, 'new' => $photoPath];
                $user->photo = $photoPath;
            }
        }

        if (!$user->save(false)) {
            return false;
        }

        // Registra histórico
        foreach ($changedFields as $field => $values) {
            $type = ($field === 'photo') ? 'photo' : 'update';
            ProfileHistory::logChange($user->id, $field, $values['old'], $values['new'], $type);
        }

        return true;
    }
}
```

### ChangePasswordForm

Formulário para alteração de palavra-passe.

```php
<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\ProfileHistory;

class ChangePasswordForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $confirmPassword;

    public function rules()
    {
        return [
            [['currentPassword', 'newPassword', 'confirmPassword'], 'required'],
            ['currentPassword', 'validateCurrentPassword'],
            ['newPassword', 'string', 'min' => 6, 'max' => 72],
            ['newPassword', 'match', 
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'message' => 'Deve conter maiúscula, minúscula e número.'
            ],
            ['confirmPassword', 'compare', 'compareAttribute' => 'newPassword'],
            ['newPassword', 'validateNotSameAsCurrent'],
        ];
    }

    public function validateCurrentPassword($attribute)
    {
        $user = Yii::$app->user->identity;
        if (!$user->validatePassword($this->currentPassword)) {
            $this->addError($attribute, 'Senha atual incorreta.');
        }
    }

    public function changePassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = Yii::$app->user->identity;
        $user->setPassword($this->newPassword);
        
        if ($user->save(false)) {
            ProfileHistory::logChange($user->id, 'password', '***', '***', 'password');
            return true;
        }
        return false;
    }
}
```

### ProfileHistory

Model para auditoria de alterações.

```php
<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class ProfileHistory extends ActiveRecord
{
    const TYPE_UPDATE = 'update';
    const TYPE_PASSWORD = 'password';
    const TYPE_PHOTO = 'photo';

    public static function tableName()
    {
        return '{{%profile_history}}';
    }

    public function rules()
    {
        return [
            [['user_id', 'field_name'], 'required'],
            [['user_id'], 'integer'],
            [['old_value', 'new_value'], 'string'],
            [['field_name'], 'string', 'max' => 50],
            [['change_type'], 'in', 'range' => ['update', 'password', 'photo']],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
        ];
    }

    /**
     * Registra uma alteração no histórico
     */
    public static function logChange($userId, $fieldName, $oldValue, $newValue, $changeType = 'update')
    {
        $history = new self();
        $history->user_id = $userId;
        $history->field_name = $fieldName;
        $history->old_value = (string)$oldValue;
        $history->new_value = (string)$newValue;
        $history->change_type = $changeType;
        $history->ip_address = Yii::$app->request->userIP;
        $history->user_agent = substr(Yii::$app->request->userAgent ?? '', 0, 255);
        
        return $history->save();
    }

    public function getFieldLabel()
    {
        $labels = [
            'name' => 'Nome',
            'email' => 'Email',
            'phone' => 'Telefone',
            'photo' => 'Foto de Perfil',
            'password' => 'Palavra-passe',
            'license_number' => 'Número da Carta',
            'license_expiry' => 'Validade da Carta',
        ];
        return $labels[$this->field_name] ?? $this->field_name;
    }

    public function getChangeTypeLabel()
    {
        return [
            'update' => 'Atualização de Dados',
            'password' => 'Alteração de Senha',
            'photo' => 'Atualização de Foto',
        ][$this->change_type] ?? $this->change_type;
    }
}
```

---

## Tabela de Base de Dados

### profile_history

```sql
CREATE TABLE `profile_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `field_name` VARCHAR(50) NOT NULL COMMENT 'Campo alterado',
    `old_value` TEXT COMMENT 'Valor anterior',
    `new_value` TEXT COMMENT 'Novo valor',
    `change_type` ENUM('update','password','photo') DEFAULT 'update',
    `ip_address` VARCHAR(45) COMMENT 'IP do utilizador',
    `user_agent` VARCHAR(255) COMMENT 'Browser/dispositivo',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX `idx_profile_history_user` (`user_id`),
    INDEX `idx_profile_history_type` (`change_type`),
    INDEX `idx_profile_history_date` (`created_at`),
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Upload de Foto

### Configuração

O sistema usa o **FileUpload nativo do Yii2** para gestão de ficheiros.

### Diretório de Armazenamento

```
frontend/web/uploads/avatars/
├── .gitignore           # Ignora ficheiros, mantém pasta
├── avatar_1_1704xxx.jpg # Formato: avatar_{userId}_{timestamp}.ext
└── avatar_2_1704xxx.png
```

### Validações de Upload

| Parâmetro | Valor |
|-----------|-------|
| Extensões | png, jpg, jpeg, gif |
| Tamanho máximo | 2 MB |
| MIME types | image/png, image/jpeg, image/gif |

### Fluxo de Upload

```
1. Utilizador seleciona ficheiro
2. JavaScript mostra preview
3. Submissão do formulário
4. Validação no servidor (ProfileForm)
5. Remoção da foto antiga (se existir)
6. Gravação da nova foto
7. Atualização do campo photo no User
8. Registo no histórico
```

---

## Views

### index.php (Visualização)

Exibe informações completas do utilizador:
- Foto de perfil (ou avatar gerado)
- Dados pessoais (nome, email, telefone)
- Função e estado
- Dados de condutor (se aplicável)
- Histórico recente de alterações
- Ações rápidas

### update.php (Edição)

Formulário com:
- Nome e email
- Telefone
- Dados da carta de condução
- Upload de foto com preview
- Validação em tempo real

### change-password.php (Alteração de Senha)

- Campo de senha atual
- Nova senha com requisitos
- Confirmação de senha
- Indicador visual de força
- Lista de requisitos com checkmarks

### history.php (Histórico)

GridView com:
- Data/hora da alteração
- Tipo (badge colorido)
- Campo alterado
- Valor anterior/novo
- IP do utilizador

---

## Integração com Layout

### Menu Lateral (dashboard.php)

```php
<li class="nav-header">CONTA</li>
<li class="nav-item">
    <a href="<?= Url::to(['profile/index']) ?>" class="nav-link">
        <i class="nav-icon fas fa-user-circle"></i>
        <p>Meu Perfil</p>
    </a>
</li>
```

### Dropdown do Utilizador

```php
<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown">
        <i class="fas fa-user-circle"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <a href="<?= Url::to(['profile/index']) ?>" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Meu Perfil
        </a>
        <a href="<?= Url::to(['profile/change-password']) ?>" class="dropdown-item">
            <i class="fas fa-key mr-2"></i> Alterar Senha
        </a>
        <div class="dropdown-divider"></div>
        <a href="<?= Url::to(['dashboard/logout']) ?>" class="dropdown-item">
            <i class="fas fa-sign-out-alt mr-2"></i> Sair
        </a>
    </div>
</li>
```

---

## Segurança

### Validações Implementadas

| Aspecto | Implementação |
|---------|---------------|
| Autenticação | Todas as rotas requerem `@` (login) |
| Email único | Validação por empresa (company_id) |
| Senha forte | Mín. 6 chars, maiúscula, minúscula, número |
| Senha atual | Obrigatória para alteração |
| CSRF | Proteção nativa do Yii2 |
| XSS | Escape com `Html::encode()` |
| Upload seguro | Validação de extensão e MIME |

### Auditoria

Todas as alterações registam:
- ID do utilizador
- Campo alterado
- Valor anterior e novo
- Tipo de alteração
- IP do cliente
- User-Agent do browser
- Data/hora (UTC)

---

## Testes

### Testar Visualização

```bash
curl -s -I http://localhost:8001/profile/index
# Esperado: HTTP 200 OK (ou redirect para login)
```

### Testar Alteração de Senha

1. Aceder a `/profile/change-password`
2. Inserir senha atual correta
3. Inserir nova senha válida
4. Verificar redirect e mensagem de sucesso
5. Confirmar login com nova senha

### Testar Upload de Foto

1. Aceder a `/profile/update`
2. Selecionar imagem válida (< 2MB)
3. Verificar preview
4. Submeter e confirmar foto no perfil

---

**Versão:** 1.0  
**Data:** Janeiro 2026  
**Autor:** Sistema VeiGest
