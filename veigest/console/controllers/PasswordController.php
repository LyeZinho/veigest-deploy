<?php
namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\User;

class PasswordController extends Controller
{
    public function actionReset($username = 'admin', $newPassword = 'admin')
    {
        echo "🔐 Alterando password do utilizador '$username'...\n";
        
        $user = User::findByUsername($username);
        if (!$user) {
            echo "❌ Utilizador '$username' não encontrado!\n";
            return 1;
        }
        
        echo "👤 Utilizador encontrado: {$user->nome} (ID: {$user->id})\n";
        echo "📧 Email: {$user->email}\n";
        
        // Atualizar password
        $user->setPassword($newPassword);
        $user->generateAuthKey();
        
        if ($user->save()) {
            echo "✅ Password alterada com sucesso!\n";
            echo "🔑 Nova password: $newPassword\n";
            echo "🌐 Pode agora fazer login em: http://localhost/site/login\n";
            return 0;
        } else {
            echo "❌ Erro ao alterar password:\n";
            foreach ($user->getErrors() as $field => $errors) {
                foreach ($errors as $error) {
                    echo "  - $field: $error\n";
                }
            }
            return 1;
        }
    }
    
    public function actionInfo($username = 'admin')
    {
        echo "ℹ️  Informações do utilizador '$username'...\n";
        
        $user = User::findByUsername($username);
        if (!$user) {
            echo "❌ Utilizador '$username' não encontrado!\n";
            return 1;
        }
        
        echo "👤 Nome: {$user->nome}\n";
        echo "📧 Email: {$user->email}\n";
        echo "🆔 ID: {$user->id}\n";
        echo "🏢 Company ID: {$user->company_id}\n";
        echo "🔐 Password Hash: {$user->password_hash}\n";
        echo "🔑 Auth Key: {$user->auth_key}\n";
        echo "📊 Status: {$user->status}\n";
        
        // Testar se a password 'admin' funciona
        if ($user->validatePassword('admin')) {
            echo "✅ Password 'admin' está CORRETA\n";
        } else {
            echo "❌ Password 'admin' está INCORRETA\n";
        }
        
        return 0;
    }
}