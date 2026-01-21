<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\User;

class UserController extends Controller
{
    public function actionCreate($username = 'admin', $email = 'admin@veigest.pt', $password = '123456')
    {
        echo "🚀 Criando utilizador VeiGest...\n";

        // Verificar se já existe
        $existingUser = User::findByUsername($username);
        if ($existingUser) {
            echo "✅ Utilizador '$username' já existe (ID: {$existingUser->id})\n";
            echo "📧 Email: {$existingUser->email}\n";
            return 0;
        }

        // Verificar se há empresas
        $companyExists = Yii::$app->db->createCommand("SELECT COUNT(*) FROM companies")->queryScalar();
        if ($companyExists == 0) {
            echo "⚠️  Nenhuma empresa encontrada. Criando empresa padrão...\n";
            Yii::$app->db->createCommand()
                ->insert('companies', [
                    'nome' => 'VeiGest Empresa Padrão',
                    'email' => 'admin@veigest.pt',
                    'nif' => '999999999',
                    'telefone' => '123456789',
                    'morada' => 'Rua Exemplo, 123',
                    'cidade' => 'Lisboa',
                    'codigo_postal' => '1000-000',
                    'pais' => 'Portugal',
                    'estado' => 'ativo',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ])
                ->execute();
            echo "✅ Empresa padrão criada\n";
        }

        // Criar utilizador
        $user = new User();
        $user->username = $username;
        $user->nome = $username;
        $user->email = $email;
        $user->status = 'active';
        $user->company_id = 1;

        // Definir password
        $user->setPassword($password);
        $user->generateAuthKey();

        if ($user->save()) {
            echo "✅ Utilizador criado com sucesso!\n";
            echo "👤 Username: $username\n";
            echo "📧 Email: $email\n";
            echo "🔐 Password: $password\n";
            echo "🏢 Company ID: 1\n";
            echo "🆔 User ID: {$user->id}\n";
            echo "\n";
            echo "🌐 Acesso Frontend: http://localhost/site/login\n";
            echo "🔧 Acesso Backend: http://localhost:8080/site/login\n";
            return 0;
        } else {
            echo "❌ Erro ao criar utilizador:\n";
            foreach ($user->getErrors() as $field => $errors) {
                foreach ($errors as $error) {
                    echo "  - $field: $error\n";
                }
            }
            return 1;
        }
    }

    // Gera auth_key para todos os utilizadores que não têm.
    public function actionGenerateAuthKeys()
    {
        $users = User::find()->where(['auth_key' => null])->orWhere(['auth_key' => ''])->all();

        if (empty($users)) {
            $this->stdout("Todos os utilizadores já têm auth_key.\n");
            return;
        }

        foreach ($users as $user) {
            $user->generateAuthKey();
            if ($user->save(false)) {
                $this->stdout("auth_key gerada para utilizador: {$user->nome}\n");
            } else {
                $this->stderr("Erro ao salvar: {$user->nome}\n");
            }
        }

        $this->stdout("DONE!\n");
    }
}
