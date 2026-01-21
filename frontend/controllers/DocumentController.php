<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Document;
use common\models\File;
use common\models\Vehicle;
use common\models\User;
use frontend\models\DocumentSearch;
use frontend\models\DocumentUploadForm;

/**
 * DocumentController - Document Management (CRUD)
 * 
 * Access Control:
 * - Admin: NO ACCESS (frontend blocked)
 * - Manager: FULL ACCESS (view, create, update, delete)
 * - Driver: NO ACCESS (documents not visible to drivers)
 */
class DocumentController extends Controller
{
    /**
     * @var string
     */
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
                    // Block admin from frontend
                    [
                        'allow' => false,
                        'roles' => ['admin'],
                        'denyCallback' => function ($rule, $action) {
                            throw new ForbiddenHttpException(
                                'Administrators do not have access to the frontend.'
                            );
                        },
                    ],
                    // View documents - manager only (driver has documents.view but limited)
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'download'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('documents.view');
                        },
                    ],
                    // Create documents - manager only
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('documents.create');
                        },
                    ],
                    // Update documents - manager only
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('documents.update');
                        },
                    ],
                    // Delete documents - manager only
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('documents.delete');
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lista todos os documentos.
     * 
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new DocumentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        // Estatísticas
        $companyId = Yii::$app->user->identity->company_id;
        $stats = Document::getStatsByCompany($companyId);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'stats' => $stats,
        ]);
    }

    /**
     * Exibe um documento específico.
     * 
     * @param int $id
     * @return string
     * @throws NotFoundHttpException se o documento não for encontrado
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Cria um novo documento.
     * 
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new DocumentUploadForm();
        $companyId = Yii::$app->user->identity->company_id;

        // Listas para dropdowns
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();
        
        $drivers = User::find()
            ->where(['company_id' => $companyId, 'roles' => 'condutor'])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->uploadedFile = UploadedFile::getInstance($model, 'uploadedFile');

            if ($document = $model->upload()) {
                Yii::$app->session->setFlash('success', 'Documento enviado com sucesso!');
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    /**
     * Atualiza um documento existente.
     * 
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException se o documento não for encontrado
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $companyId = Yii::$app->user->identity->company_id;

        // Listas para dropdowns
        $vehicles = Vehicle::find()
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->all();
        
        $drivers = User::find()
            ->where(['company_id' => $companyId, 'roles' => 'condutor'])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Documento atualizado com sucesso!');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ]);
    }

    /**
     * Elimina um documento.
     * 
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException se o documento não for encontrado
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Documento eliminado com sucesso!');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Erro ao eliminar o documento.');
            Yii::error('Erro ao eliminar documento: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Download do ficheiro de um documento.
     * 
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException se o documento não for encontrado
     */
    public function actionDownload($id)
    {
        $model = $this->findModel($id);
        $file = $model->file;

        if (!$file) {
            throw new NotFoundHttpException('Ficheiro não encontrado.');
        }

        $filePath = $file->getAbsolutePath();

        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('Ficheiro não encontrado no servidor.');
        }

        return Yii::$app->response->sendFile($filePath, $file->original_name);
    }

    /**
     * Retorna veículos e motoristas para AJAX (usado no formulário)
     * 
     * @return array
     */
    public function actionGetOptions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $companyId = Yii::$app->user->identity->company_id;

        $vehicles = Vehicle::find()
            ->select(['id', 'license_plate', 'brand', 'model'])
            ->where(['company_id' => $companyId])
            ->orderBy(['license_plate' => SORT_ASC])
            ->asArray()
            ->all();
        
        $drivers = User::find()
            ->select(['id', 'name'])
            ->where(['company_id' => $companyId, 'roles' => 'condutor'])
            ->orderBy(['name' => SORT_ASC])
            ->asArray()
            ->all();

        return [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
        ];
    }

    /**
     * Encontra o modelo Document baseado no ID.
     * 
     * @param int $id
     * @return Document o modelo encontrado
     * @throws NotFoundHttpException se o modelo não for encontrado ou não pertence à empresa do utilizador
     */
    protected function findModel($id)
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $model = Document::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('O documento solicitado não existe.');
        }

        return $model;
    }
}
