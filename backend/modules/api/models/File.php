<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use common\models\User;

/**
 * File API model
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $original_name
 * @property integer $size
 * @property string $path
 * @property integer $uploaded_by
 * @property string $created_at
 */
class File extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%files}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['company_id', 'original_name', 'size', 'path', 'uploaded_by'], 'required'],
            [['company_id', 'size', 'uploaded_by'], 'integer'],
            [['original_name'], 'string', 'max' => 255],
            [['path'], 'string', 'max' => 500],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['uploaded_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'original_name' => 'Nome Original',
            'size' => 'Tamanho',
            'path' => 'Caminho',
            'uploaded_by' => 'Enviado por',
            'created_at' => 'Criado em',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'company_id',
            'original_name',
            'size',
            'size_formatted' => function ($model) {
                return $this->formatFileSize($model->size);
            },
            'path',
            'uploaded_by',
            'extension' => function ($model) {
                return pathinfo($model->original_name, PATHINFO_EXTENSION);
            },
            'created_at',
        ];
    }

    /**
     * Extra fields
     */
    public function extraFields()
    {
        return [
            'company',
            'uploader',
            'documents',
        ];
    }

    /**
     * Get company relationship
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Get uploader relationship
     */
    public function getUploader()
    {
        return $this->hasOne(User::class, ['id' => 'uploaded_by']);
    }

    /**
     * Get documents relationship
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['file_id' => 'id']);
    }

    /**
     * Format file size to human readable
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
