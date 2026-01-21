<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\models\ProfileHistory;

/**
 * Formulário para alteração de palavra-passe.
 * 
 * RF-FO-003.3: Alteração de palavra-passe
 */
class ChangePasswordForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $confirmPassword;

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
            [['currentPassword', 'newPassword', 'confirmPassword'], 'required'],
            ['currentPassword', 'validateCurrentPassword'],
            ['newPassword', 'string', 'min' => 6, 'max' => 72],
            ['newPassword', 'match', 
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'message' => 'A palavra-passe deve conter pelo menos uma letra maiúscula, uma minúscula e um número.'
            ],
            ['confirmPassword', 'compare', 'compareAttribute' => 'newPassword', 'message' => 'As palavras-passe não coincidem.'],
            ['newPassword', 'validateNotSameAsCurrent'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'currentPassword' => 'Palavra-passe Atual',
            'newPassword' => 'Nova Palavra-passe',
            'confirmPassword' => 'Confirmar Nova Palavra-passe',
        ];
    }

    /**
     * Valida a palavra-passe atual
     */
    public function validateCurrentPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->currentPassword)) {
                $this->addError($attribute, 'A palavra-passe atual está incorreta.');
            }
        }
    }

    /**
     * Valida se a nova palavra-passe é diferente da atual
     */
    public function validateNotSameAsCurrent($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if ($user && $user->validatePassword($this->newPassword)) {
                $this->addError($attribute, 'A nova palavra-passe deve ser diferente da atual.');
            }
        }
    }

    /**
     * Altera a palavra-passe do utilizador
     * 
     * @return bool
     */
    public function changePassword()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->getUser();
        $user->setPassword($this->newPassword);
        
        if (!$user->save(false)) {
            return false;
        }

        // Regista no histórico
        ProfileHistory::logChange(
            $user->id,
            'password',
            '********',
            '********',
            ProfileHistory::TYPE_PASSWORD
        );

        return true;
    }

    /**
     * Retorna o utilizador atual
     * 
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Yii::$app->user->identity;
        }
        return $this->_user;
    }
}
