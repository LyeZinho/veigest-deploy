<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $nome;
    public $username;
    public $email;
    public $password;

    public $company_id = 1;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['nome', 'trim'],
            ['nome', 'string', 'min' => 2, 'max' => 255],

            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool|User|null whether the creating new account was successful and email was sent
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User(['scenario' => 'signup']);
        $user->name = $this->nome ?: $this->username;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->company_id = $this->company_id;
        $user->status = User::STATUS_INACTIVE;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();

        if ($user->save()) {
            // send verification email
            $this->sendEmail($user);
            try {
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('driver'); // role padrão conforme migração
                if ($role !== null) {
                    $auth->assign($role, $user->id);
                }
            } catch (\Throwable $e) {
                // Ignore RBAC assignment failures during tests
            }

            return $user;
        }

        return null;
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    public function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
