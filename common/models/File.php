<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "files".
 *
 * @property int $id
 * @property int $company_id
 * @property string $original_name
 * @property int $size
 * @property string $path
 * @property int $uploaded_by
 * @property string $created_at
 *
 * @property Company $company
 * @property User $uploadedBy
 * @property Document[] $documents
 */
class File extends ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $uploadedFile;

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
            [['company_id', 'original_name', 'size', 'path', 'uploaded_by'], 'required', 'except' => 'upload'],
            [['company_id', 'size', 'uploaded_by'], 'integer'],
            [['created_at'], 'safe'],
            [['original_name'], 'string', 'max' => 255],
            [['path'], 'string', 'max' => 500],
            [['company_id'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['uploaded_by' => 'id']],
            
            // Regra de upload - aceita documentos comuns
            [['uploadedFile'], 'file', 
                'skipOnEmpty' => false, 
                'extensions' => 'pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif',
                'maxSize' => 10 * 1024 * 1024, // 10MB
                'checkExtensionByMimeType' => false, // Evita problemas com MIME types
                'on' => 'upload'
            ],
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
            'uploaded_by' => 'Enviado Por',
            'created_at' => 'Data de Upload',
            'uploadedFile' => 'Ficheiro',
        ];
    }

    /**
     * Gets query for [[Company]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * Gets query for [[UploadedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedBy()
    {
        return $this->hasOne(User::class, ['id' => 'uploaded_by']);
    }

    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['file_id' => 'id']);
    }

    /**
     * Processa o upload do ficheiro
     * 
     * @param int $companyId ID da empresa
     * @param int $userId ID do utilizador
     * @return bool
     */
    public function upload($companyId, $userId)
    {
        if (!$this->validate()) {
            return false;
        }

        // Criar diretório de uploads se não existir
        $uploadPath = $this->getUploadBasePath($companyId);
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Gerar nome único para o ficheiro
        $fileName = Yii::$app->security->generateRandomString(16) . '_' . time();
        $fileName .= '.' . $this->uploadedFile->extension;
        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        // Salvar o ficheiro
        if ($this->uploadedFile->saveAs($fullPath)) {
            $this->company_id = $companyId;
            $this->original_name = $this->uploadedFile->baseName . '.' . $this->uploadedFile->extension;
            $this->size = $this->uploadedFile->size;
            $this->path = $this->getRelativePath($companyId, $fileName);
            $this->uploaded_by = $userId;
            $this->created_at = date('Y-m-d H:i:s');
            
            return $this->save(false);
        }

        return false;
    }

    /**
     * Retorna o caminho base para uploads
     * 
     * @param int $companyId
     * @return string
     */
    public function getUploadBasePath($companyId)
    {
        return Yii::getAlias('@frontend/web/uploads/documents/' . $companyId);
    }

    /**
     * Retorna o caminho relativo do ficheiro
     * 
     * @param int $companyId
     * @param string $fileName
     * @return string
     */
    public function getRelativePath($companyId, $fileName)
    {
        return 'uploads/documents/' . $companyId . '/' . $fileName;
    }

    /**
     * Retorna a URL completa para download do ficheiro
     * 
     * @return string
     */
    public function getDownloadUrl()
    {
        return Yii::getAlias('@web/' . $this->path);
    }

    /**
     * Retorna o caminho absoluto do ficheiro
     * 
     * @return string
     */
    public function getAbsolutePath()
    {
        return Yii::getAlias('@frontend/web/' . $this->path);
    }

    /**
     * Retorna o tamanho formatado do ficheiro
     * 
     * @return string
     */
    public function getFormattedSize()
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Retorna a extensão do ficheiro
     * 
     * @return string
     */
    public function getExtension()
    {
        return strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    /**
     * Retorna o ícone apropriado para o tipo de ficheiro
     * 
     * @return string
     */
    public function getFileIcon()
    {
        $ext = $this->getExtension();
        $icons = [
            'pdf' => 'fa-file-pdf text-danger',
            'doc' => 'fa-file-word text-primary',
            'docx' => 'fa-file-word text-primary',
            'xls' => 'fa-file-excel text-success',
            'xlsx' => 'fa-file-excel text-success',
            'jpg' => 'fa-file-image text-info',
            'jpeg' => 'fa-file-image text-info',
            'png' => 'fa-file-image text-info',
            'gif' => 'fa-file-image text-info',
        ];
        
        return $icons[$ext] ?? 'fa-file text-secondary';
    }

    /**
     * Elimina o ficheiro físico antes de eliminar o registo
     * 
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        
        // Eliminar ficheiro físico
        $filePath = $this->getAbsolutePath();
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
        
        return true;
    }
}
