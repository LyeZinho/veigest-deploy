<?php

namespace frontend\models;

use yii\db\ActiveRecord;

/**
 * Document model
 * 
 * @property integer $id
 * @property integer $company_id
 * @property integer $vehicle_id
 * @property string $tipo
 * @property string $file_path
 * @property string $status
 * @property string $expires_at
 * @property string $created_at
 * @property string $updated_at
 */
class Document extends ActiveRecord
{
    public const STATUS_VALID = 'valido';
    public const STATUS_DUE_SOON = 'validade_proxima';
    public const STATUS_EXPIRED = 'expirado';

    public static function tableName()
    {
        return '{{%documents}}';
    }

    public function rules()
    {
        return [
            [['company_id', 'vehicle_id', 'tipo', 'file_path'], 'required'],
            ['company_id', 'integer'],
            ['vehicle_id', 'integer'],
            ['tipo', 'string', 'max' => 50],
            ['file_path', 'string'],
            ['status', 'string'],
            ['expires_at', 'date'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'vehicle_id' => 'Veículo',
            'tipo' => 'Tipo',
            'file_path' => 'Ficheiro',
            'status' => 'Estado',
            'expires_at' => 'Validade',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }
}
