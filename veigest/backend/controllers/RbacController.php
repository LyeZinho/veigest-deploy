<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\models\User;

class RbacController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Alinhar com migração consolidada: usar roles existentes
        $admin  = $auth->getRole('admin');
        $manager = $auth->getRole('manager');
        $maint   = $auth->getRole('maintenance-manager');
        $senior  = $auth->getRole('senior-driver');
        $driver  = $auth->getRole('driver');

        // ATRIBUIÇÕES DE ROLES A UTILIZADORES DEMO (SE EXISTIREM)
        // Admin para utilizador ID 1 (padrão)
        if ($admin) {
            if (!$auth->getAssignment('admin', 1)) {
                $auth->assign($admin, 1);
            }
        }

        // Manager: tenta encontrar utilizador "manager"
        if ($manager) {
            $managerUser = User::find()
                ->where(['username' => 'manager'])
                ->orWhere(['email' => 'manager@veigest.com'])
                ->one();
            if ($managerUser && !$auth->getAssignment('manager', (string)$managerUser->id)) {
                $auth->assign($manager, (string)$managerUser->id);
            }
        }

        // Maintenance manager
        if ($maint) {
            $maintUser = User::find()
                ->where(['username' => 'maintenance'])
                ->orWhere(['email' => 'maintenance@veigest.com'])
                ->one();
            if ($maintUser && !$auth->getAssignment('maintenance-manager', (string)$maintUser->id)) {
                $auth->assign($maint, (string)$maintUser->id);
            }
        }

        // Senior driver
        if ($senior) {
            $seniorUser = User::find()
                ->where(['username' => 'senior'])
                ->orWhere(['email' => 'senior@veigest.com'])
                ->one();
            if ($seniorUser && !$auth->getAssignment('senior-driver', (string)$seniorUser->id)) {
                $auth->assign($senior, (string)$seniorUser->id);
            }
        }

        // Drivers
        if ($driver) {
            foreach (['driver1' => 'driver1@veigest.com', 'driver2' => 'driver2@veigest.com', 'driver3' => 'driver3@veigest.com'] as $uname => $email) {
                $u = User::find()->where(['username' => $uname])->orWhere(['email' => $email])->one();
                if ($u && !$auth->getAssignment('driver', (string)$u->id)) {
                    $auth->assign($driver, (string)$u->id);
                }
            }
        }

        echo "<h3>RBAC assignments updated com sucesso!</h3>";
        exit;
    }
}
