<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use common\models\File;
use common\models\Document;

/**
 * DocumentUploadForm é o modelo para o formulário de upload de documentos.
 * Combina o upload do ficheiro com os metadados do documento.
 */
class DocumentUploadForm extends Model
{
    /**
     * @var UploadedFile o ficheiro a ser enviado
     */
    public $uploadedFile;

    /**
     * @var int|null ID do veículo associado
     */
    public $vehicle_id;

    /**
     * @var int|null ID do motorista associado
     */
    public $driver_id;

    /**
     * @var string tipo de documento
     */
    public $type;

    /**
     * @var string|null data de validade
     */
    public $expiry_date;

    /**
     * @var string|null observações
     */
    public $notes;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uploadedFile', 'type'], 'required'],
            [['vehicle_id', 'driver_id'], 'integer'],
            [['type'], 'in', 'range' => array_keys(Document::getTypesList())],
            [['expiry_date'], 'date', 'format' => 'php:Y-m-d'],
            [['notes'], 'string', 'max' => 1000],
            
            // Validação do ficheiro
            [['uploadedFile'], 'file', 
                'skipOnEmpty' => false, 
                'extensions' => 'pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif',
                'maxSize' => 10 * 1024 * 1024, // 10MB
                'checkExtensionByMimeType' => false,
                'tooBig' => 'O ficheiro é muito grande. Tamanho máximo: 10MB.',
                'wrongExtension' => 'Extensão não permitida. Extensões aceites: pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uploadedFile' => 'Ficheiro',
            'vehicle_id' => 'Veículo',
            'driver_id' => 'Motorista',
            'type' => 'Tipo de Documento',
            'expiry_date' => 'Data de Validade',
            'notes' => 'Observações',
        ];
    }

    /**
     * Processa o upload e cria o documento
     * 
     * @return Document|null O documento criado ou null em caso de erro
     */
    public function upload()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = Yii::$app->user->identity;
        $companyId = $user->company_id;
        $userId = $user->id;

        // Criar diretório se não existir
        $uploadPath = Yii::getAlias('@frontend/web/uploads/documents/' . $companyId);
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                $this->addError('uploadedFile', 'Não foi possível criar o diretório de uploads.');
                return null;
            }
        }

        // Gerar nome único para o ficheiro
        $fileName = Yii::$app->security->generateRandomString(16) . '_' . time();
        $fileName .= '.' . $this->uploadedFile->extension;
        $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        // Salvar o ficheiro
        if (!$this->uploadedFile->saveAs($fullPath)) {
            $this->addError('uploadedFile', 'Erro ao salvar o ficheiro.');
            return null;
        }

        // Iniciar transação
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Criar registo na tabela files
            $file = new File();
            $file->company_id = $companyId;
            $file->original_name = $this->uploadedFile->baseName . '.' . $this->uploadedFile->extension;
            $file->size = $this->uploadedFile->size;
            $file->path = 'uploads/documents/' . $companyId . '/' . $fileName;
            $file->uploaded_by = $userId;
            $file->created_at = date('Y-m-d H:i:s');

            if (!$file->save()) {
                throw new \Exception('Erro ao salvar registo do ficheiro: ' . json_encode($file->errors));
            }

            // Criar registo na tabela documents
            $document = new Document();
            $document->company_id = $companyId;
            $document->file_id = $file->id;
            $document->vehicle_id = $this->vehicle_id ?: null;
            $document->driver_id = $this->driver_id ?: null;
            $document->type = $this->type;
            $document->expiry_date = $this->expiry_date ?: null;
            $document->notes = $this->notes;

            if (!$document->save()) {
                throw new \Exception('Erro ao salvar documento: ' . json_encode($document->errors));
            }

            $transaction->commit();
            return $document;

        } catch (\Exception $e) {
            $transaction->rollBack();
            
            // Remover ficheiro físico em caso de erro
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            
            Yii::error('Erro no upload de documento: ' . $e->getMessage());
            $this->addError('uploadedFile', 'Erro ao processar o documento. Por favor, tente novamente.');
            return null;
        }
    }
}
