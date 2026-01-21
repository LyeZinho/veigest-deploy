<?php

namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var User
     */
    private $_user;


    /**
     * Creates a form model with given token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }
        $this->_user = User::findByVerificationToken($token);
        try {
            $raw = User::find()->select(['username','status','verification_token'])->where(['verification_token'=>$token])->asArray()->one();
            @file_put_contents(\Yii::getAlias('@frontend/runtime').'/verify_debug.txt', date('c')." token=".$token." found=".(bool)$this->_user." statusAR=".($this->_user->status ?? 'null')." raw=".var_export($raw,true)."\n", FILE_APPEND);
        } catch (\Throwable $e) {}
        if (!$this->_user || (int)$this->_user->status !== User::STATUS_INACTIVE) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }
        parent::__construct($config);
    }

    /**
     * Verify email
     *
     * @return User|null the saved model or null if saving fails
     */
    public function verifyEmail()
    {
        $user = $this->_user;
        $user->status = User::STATUS_ACTIVE;
        return $user->save(false) ? $user : null;
    }
}
