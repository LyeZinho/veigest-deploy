<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use common\models\User;
use common\models\ProfileHistory;

/**
 * Formulário para edição de perfil pessoal.
 * 
 * RF-FO-003: Gestão de Perfil Pessoal
 * - RF-FO-003.2: Edição de informações de contacto
 * - RF-FO-003.4: Upload de foto de perfil
 */
class ProfileForm extends Model
{
    public $name;
    public $email;
    public $phone;
    public $license_number;
    public $license_expiry;
    
    /**
     * @var UploadedFile
     */
    public $photoFile;

    /**
     * @var User
     */
    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['email', 'email'],
            ['email', 'validateUniqueEmail'],
            [['name'], 'string', 'max' => 150],
            [['phone'], 'string', 'max' => 20],
            [['phone'], 'match', 'pattern' => '/^[\+]?[0-9\s\-\(\)]+$/', 'message' => 'Formato de telefone inválido.'],
            [['license_number'], 'string', 'max' => 50],
            [['license_expiry'], 'date', 'format' => 'php:Y-m-d'],
            [['photoFile'], 'file', 
                'skipOnEmpty' => true, 
                'extensions' => 'png, jpg, jpeg, gif', 
                'maxSize' => 2 * 1024 * 1024, // 2MB
                'mimeTypes' => ['image/png', 'image/jpeg', 'image/gif'],
                'wrongMimeType' => 'Apenas imagens PNG, JPG ou GIF são permitidas.',
                'tooBig' => 'O ficheiro não pode exceder 2MB.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Nome Completo',
            'email' => 'Email',
            'phone' => 'Telefone',
            'license_number' => 'Número da Carta de Condução',
            'license_expiry' => 'Validade da Carta',
            'photoFile' => 'Foto de Perfil',
        ];
    }

    /**
     * Valida se o email é único (exceto para o próprio utilizador)
     */
    public function validateUniqueEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            $exists = User::find()
                ->where(['email' => $this->email, 'company_id' => $user->company_id])
                ->andWhere(['!=', 'id', $user->id])
                ->exists();
            
            if ($exists) {
                $this->addError($attribute, 'Este email já está em uso por outro utilizador.');
            }
        }
    }

    /**
     * Carrega dados do utilizador para o formulário
     * 
     * @param User $user
     */
    public function loadFromUser(User $user)
    {
        $this->_user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->license_number = $user->license_number;
        $this->license_expiry = $user->license_expiry;
    }

    /**
     * Guarda as alterações do perfil
     * 
     * @return bool
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        $changedFields = [];

        // Verifica campos alterados para histórico
        if ($user->name !== $this->name) {
            $changedFields['name'] = ['old' => $user->name, 'new' => $this->name];
            $user->name = $this->name;
        }

        if ($user->email !== $this->email) {
            $changedFields['email'] = ['old' => $user->email, 'new' => $this->email];
            $user->email = $this->email;
        }

        if ($user->phone !== $this->phone) {
            $changedFields['phone'] = ['old' => $user->phone, 'new' => $this->phone];
            $user->phone = $this->phone;
        }

        if ($user->license_number !== $this->license_number) {
            $changedFields['license_number'] = ['old' => $user->license_number, 'new' => $this->license_number];
            $user->license_number = $this->license_number;
        }

        if ($user->license_expiry !== $this->license_expiry) {
            $changedFields['license_expiry'] = ['old' => $user->license_expiry, 'new' => $this->license_expiry];
            $user->license_expiry = $this->license_expiry;
        }

        // Upload de foto
        if ($this->photoFile) {
            $oldPhoto = $user->photo;
            $photoPath = $this->uploadPhoto();
            if ($photoPath) {
                $changedFields['photo'] = ['old' => $oldPhoto, 'new' => $photoPath];
                $user->photo = $photoPath;
            }
        }

        // Guarda utilizador
        if (!$user->save(false)) {
            return false;
        }

        // Regista histórico de alterações
        foreach ($changedFields as $field => $values) {
            $changeType = ($field === 'photo') ? ProfileHistory::TYPE_PHOTO : ProfileHistory::TYPE_UPDATE;
            ProfileHistory::logChange($user->id, $field, $values['old'], $values['new'], $changeType);
        }

        return true;
    }

    /**
     * Faz upload da foto de perfil
     * 
     * @return string|null Caminho relativo da foto ou null em caso de erro
     */
    protected function uploadPhoto()
    {
        if (!$this->photoFile) {
            return null;
        }

        $user = $this->getUser();
        
        // Diretório de upload
        $uploadDir = Yii::getAlias('@frontend/web/uploads/avatars');
        
        // Cria diretório se não existir
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0777, true)) {
                Yii::error("Não foi possível criar diretório: $uploadDir", 'profile');
                return null;
            }
        }
        
        // Verifica se o diretório é gravável
        if (!is_writable($uploadDir)) {
            // Tenta ajustar permissões
            @chmod($uploadDir, 0777);
            
            if (!is_writable($uploadDir)) {
                Yii::error("Diretório não gravável: $uploadDir", 'profile');
                $this->addError('photoFile', 'Erro de permissão ao salvar foto. Contacte o administrador.');
                return null;
            }
        }

        // Nome único do ficheiro
        $fileName = 'avatar_' . $user->id . '_' . time() . '.' . $this->photoFile->extension;
        $filePath = $uploadDir . '/' . $fileName;

        // Remove foto antiga se existir
        if ($user->photo && file_exists(Yii::getAlias('@frontend/web') . $user->photo)) {
            @unlink(Yii::getAlias('@frontend/web') . $user->photo);
        }

        // Guarda novo ficheiro
        if ($this->photoFile->saveAs($filePath)) {
            return '/uploads/avatars/' . $fileName;
        }
        
        $this->addError('photoFile', 'Erro ao guardar a foto.');
        return null;
    }

    /**
     * Retorna o utilizador atual
     * 
     * @return User
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Yii::$app->user->identity;
        }
        return $this->_user;
    }
}
