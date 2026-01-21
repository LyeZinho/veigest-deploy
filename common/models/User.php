<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $name
 * @property integer $company_id
 * @property string $email
 * @property string $password_hash
 * @property string $phone
 * @property string $status
 * @property string $auth_key
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $license_number
 * @property string $license_expiry
 * @property string $photo
 * @property string $roles
 * @property string $created_at
 * @property string $updated_at
 * @property string $password write-only password
 */



class User extends ActiveRecord implements IdentityInterface
{
    public $password;
    public $tempRole; // Propriedade temporária para receber role do formulário


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => function () {
                    return date('Y-m-d H:i:s');
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'name', 'email', 'company_id'], 'required'],
            ['username', 'unique', 'message' => 'Este nome de utilizador já está em uso.'],
            ['email', 'unique', 'message' => 'Este email já está registado no sistema.'],
            ['tempRole', 'required', 'on' => 'adminCreate', 'message' => 'Por favor selecione um papel.'],
            ['tempRole', 'safe'],
            ['password', 'required', 'on' => ['create', 'adminCreate']],
            ['password', 'string', 'min' => 3, 'message' => 'A palavra-passe deve ter pelo menos 3 caracteres.'],
            ['email', 'email', 'message' => 'Por favor insira um email válido.'],
            ['username', 'string', 'max' => 255],
            ['name', 'string', 'max' => 255],
            ['phone', 'string', 'max' => 20],
            ['tempRole', 'in', 'range' => ['admin', 'manager', 'driver'], 'message' => 'Papel inválido.'],
            ['status', 'in', 'range' => ['active', 'inactive']],
            ['status', 'default', 'value' => 'active'],
            ['company_id', 'integer'],
            ['company_id', 'exist', 'targetClass' => Company::class, 'targetAttribute' => 'id', 'message' => 'Empresa inválida.'],
        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();

        // Creation scenario → password required
        $scenarios['create'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password',
            'role',
            'status'
        ];

        // Admin creation scenario → password required
        $scenarios['adminCreate'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password',
            'tempRole',
            'status',
            'phone'
        ];

        // Update scenario → password optional
        $scenarios['update'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password',
            'tempRole',
            'status',
            'phone'
        ];

        $scenarios['signup'] = [
            'username',
            'name',
            'email',
            'company_id',
            'password'
        ];

        return $scenarios;
    }




    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => 'active']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (empty($token)) {
            return null;
        }

        // assume token is stored in auth_key (simple bearer token)
        return static::findOne(['auth_key' => $token, 'status' => 'active']);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => 'active']);
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => 'active']);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => 'active',

        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => 'inactive',

        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Generate auth_key only on creation
        if ($insert) {
            $this->generateAuthKey();
        }

        // Create password hash if filled
        if (!empty($this->password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
        }

        return true;
    }




    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Get user role from RBAC system
     * @return string|null
     */
    public function getRole()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->id);
        if (empty($roles)) {
            return null;
        }

        // Return the first role found (or the most important one)
        $roleNames = array_keys($roles);

        // Priority: admin > manager > driver
        if (in_array('admin', $roleNames)) {
            return 'admin';
        }
        if (in_array('manager', $roleNames)) {
            return 'manager';
        }
        if (in_array('driver', $roleNames)) {
            return 'driver';
        }

        return $roleNames[0] ?? null;
    }

    /**
     * Check if user has a specific role
     * @param string $roleName
     * @return bool
     */
    public function hasRole($roleName)
    {
        return Yii::$app->authManager->getAssignment($roleName, $this->id) !== null;
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'password',
            'tempRole',
        ]);
    }
}
