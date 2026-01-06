<?php

namespace backend\modules\api\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use backend\modules\api\models\File;

/**
 * File API Controller
 * 
 * Fornece operações CRUD para arquivos com multi-tenancy
 * Implementa filtragem automática por company_id
 * 
 * .
 */
class FileController extends BaseApiController
{
    public $modelClass = 'backend\modules\api\models\File';

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['delete']);
        return $actions;
    }

    /**
     * List files with filters
     * GET /api/files
     */
    public function actionIndex()
    {
        $companyId = $this->getCompanyId();
        
        $query = File::find()->where(['company_id' => $companyId]);

        // Filter by uploaded_by
        $uploadedBy = Yii::$app->request->get('uploaded_by');
        if ($uploadedBy) {
            $query->andWhere(['uploaded_by' => $uploadedBy]);
        }

        // Filter by extension
        $extension = Yii::$app->request->get('extension');
        if ($extension) {
            $query->andWhere(['LIKE', 'original_name', '%.' . $extension]);
        }

        // Search by name
        $search = Yii::$app->request->get('search');
        if ($search) {
            $query->andWhere(['LIKE', 'original_name', $search]);
        }

        // Order by
        $sort = Yii::$app->request->get('sort', '-created_at');
        if (strpos($sort, '-') === 0) {
            $query->orderBy([substr($sort, 1) => SORT_DESC]);
        } else {
            $query->orderBy([$sort => SORT_ASC]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 20),
            ],
        ]);
    }

    /**
     * View a specific file
     * GET /api/files/{id}
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }

    /**
     * Upload a new file
     * POST /api/files
     */
    public function actionCreate()
    {
        $uploadedFile = UploadedFile::getInstanceByName('file');
        
        if (!$uploadedFile) {
            return $this->errorResponse('Nenhum arquivo enviado', 400);
        }

        $companyId = $this->getCompanyId();
        $userId = $this->getUserId();

        // Create upload directory
        $uploadDir = Yii::getAlias('@backend/web/uploads/') . $companyId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . $uploadedFile->name;
        $filePath = $uploadDir . $filename;

        if ($uploadedFile->saveAs($filePath)) {
            $model = new File();
            $model->company_id = $companyId;
            $model->original_name = $uploadedFile->name;
            $model->size = $uploadedFile->size;
            $model->path = 'uploads/' . $companyId . '/' . $filename;
            $model->uploaded_by = $userId;

            if ($model->save()) {
                Yii::$app->response->setStatusCode(201);
                return $this->successResponse($model, 'Arquivo enviado com sucesso');
            } else {
                // Remove file if model save failed
                unlink($filePath);
                return $this->errorResponse('Erro ao salvar informações do arquivo', 400, $model->errors);
            }
        }

        return $this->errorResponse('Erro ao fazer upload do arquivo', 500);
    }

    /**
     * Delete a file
     * DELETE /api/files/{id}
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Check if file is used in any document
        $documentsCount = $model->getDocuments()->count();
        if ($documentsCount > 0) {
            return $this->errorResponse('Não é possível excluir. O arquivo está associado a ' . $documentsCount . ' documento(s).', 400);
        }

        // Delete physical file
        $filePath = Yii::getAlias('@backend/web/') . $model->path;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if ($model->delete()) {
            return $this->successResponse(null, 'Arquivo excluído com sucesso');
        }

        return $this->errorResponse('Erro ao excluir arquivo', 500);
    }

    /**
     * Get files statistics
     * GET /api/files/stats
     */
    public function actionStats()
    {
        $companyId = $this->getCompanyId();

        $totalFiles = File::find()->where(['company_id' => $companyId])->count();
        $totalSize = File::find()->where(['company_id' => $companyId])->sum('size') ?? 0;

        // Files by extension
        $filesByExtension = Yii::$app->db->createCommand("
            SELECT 
                LOWER(SUBSTRING_INDEX(original_name, '.', -1)) as extension,
                COUNT(*) as count,
                SUM(size) as total_size
            FROM files 
            WHERE company_id = :company_id
            GROUP BY extension
            ORDER BY count DESC
        ")->bindValue(':company_id', $companyId)->queryAll();

        // Recent uploads (last 30 days)
        $recentUploads = File::find()
            ->where(['company_id' => $companyId])
            ->andWhere(['>=', 'created_at', date('Y-m-d', strtotime('-30 days'))])
            ->count();

        return $this->successResponse([
            'total_files' => (int) $totalFiles,
            'total_size' => (int) $totalSize,
            'total_size_formatted' => $this->formatFileSize($totalSize),
            'files_by_extension' => $filesByExtension,
            'recent_uploads_30_days' => (int) $recentUploads,
        ]);
    }

    /**
     * Find model with company check
     */
    protected function findModel($id)
    {
        $companyId = $this->getCompanyId();
        
        $model = File::find()
            ->where(['id' => $id, 'company_id' => $companyId])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException('Arquivo não encontrado.');
        }

        return $model;
    }

    /**
     * Format file size
     */
    private function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
