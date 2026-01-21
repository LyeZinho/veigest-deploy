<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\common\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'There is no user with this email address.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
  public function sendEmail()
{
    /* @var $user \common\models\User */
    $user = User::findOne([
        'status' => User::STATUS_ACTIVE,
        'email' => $this->email,
    ]);

    if (!$user) {
        Yii::error("PasswordResetRequest: user not found for email: {$this->email}");
        return false;
    }

    // Se token inválido ou vazio, gere novo e salve
    if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
        $user->generatePasswordResetToken();
        if (!$user->save()) {
            Yii::error('PasswordResetRequest: failed to save user with new token. Errors: ' . json_encode($user->errors));
            return false;
        }
    }

    try {
        $result = Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();

        if (!$result) {
            Yii::error("PasswordResetRequest: mailer->send() returned false for email: {$this->email}");
        }

        return $result;
    } catch (\Throwable $e) {
        Yii::error("PasswordResetRequest: Exception while sending email: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return false;
    }
}

}
