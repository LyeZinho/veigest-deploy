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

/**
 * Controller para gestão de perfil pessoal.
 * 
 * RF-FO-003: Gestão de Perfil Pessoal
 * - RF-FO-003.1: Visualização de dados pessoais
 * - RF-FO-003.2: Edição de informações de contacto
 * - RF-FO-003.3: Alteração de palavra-passe
 * - RF-FO-003.4: Upload de foto de perfil
 * - RF-FO-003.5: Histórico de alterações
 */
class ProfileController extends Controller
{
    public $layout = 'dashboard';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Apenas utilizadores autenticados
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

    /**
     * RF-FO-003.1: Visualização de dados pessoais
     * 
     * @return string
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        
        // Histórico de alterações recentes
        $historyProvider = new ActiveDataProvider([
            'query' => ProfileHistory::find()
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('index', [
            'user' => $user,
            'historyProvider' => $historyProvider,
        ]);
    }

    /**
     * RF-FO-003.2: Edição de informações de contacto
     * RF-FO-003.4: Upload de foto de perfil
     * 
     * @return string|\yii\web\Response
     */
    public function actionUpdate()
    {
        $model = new ProfileForm();
        $model->loadFromUser(Yii::$app->user->identity);

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->photoFile = UploadedFile::getInstance($model, 'photoFile');
            
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Perfil atualizado com sucesso.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * RF-FO-003.3: Alteração de palavra-passe
     * 
     * @return string|\yii\web\Response
     */
    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();

        if ($model->load(Yii::$app->request->post()) && $model->changePassword()) {
            Yii::$app->session->setFlash('success', 'Palavra-passe alterada com sucesso.');
            return $this->redirect(['index']);
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }

    /**
     * RF-FO-003.5: Histórico de alterações
     * 
     * @return string
     */
    public function actionHistory()
    {
        $user = Yii::$app->user->identity;
        
        $dataProvider = new ActiveDataProvider([
            'query' => ProfileHistory::find()
                ->where(['user_id' => $user->id])
                ->orderBy(['created_at' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('history', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Remove a foto de perfil
     * 
     * @return \yii\web\Response
     */
    public function actionDeletePhoto()
    {
        $user = Yii::$app->user->identity;
        
        if ($user->photo) {
            $oldPhoto = $user->photo;
            $filePath = Yii::getAlias('@frontend/web') . $user->photo;
            
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            
            $user->photo = null;
            if ($user->save(false)) {
                ProfileHistory::logChange(
                    $user->id,
                    'photo',
                    $oldPhoto,
                    null,
                    ProfileHistory::TYPE_PHOTO
                );
                Yii::$app->session->setFlash('success', 'Foto de perfil removida.');
            }
        }

        return $this->redirect(['index']);
    }
}
