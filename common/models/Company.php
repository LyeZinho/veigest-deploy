<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "companies".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $tax_id
 * @property string|null $email
 * @property string|null $phone
 * @property string $status
 * @property string $plan
 * @property mixed|null $settings
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property User[] $users
 * @property Vehicle[] $vehicles
 * @property Document[] $documents
 * @property File[] $files
 */
class Company extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%companies}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'tax_id'], 'required'],
            [['code'], 'string', 'max' => 10],
            [['code'], 'unique', 'message' => 'Este código já está em uso por outra empresa.'],
            [['name'], 'string', 'max' => 200],
            [['tax_id'], 'string', 'max' => 20],
            [['tax_id'], 'unique', 'message' => 'Este NIF já está registado no sistema.'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['email'], 'unique', 'message' => 'Este email já está registado no sistema.'],
            [['phone'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => ['active', 'suspended', 'inactive']],
            [['status'], 'default', 'value' => 'active'],
            [['plan'], 'in', 'range' => ['basic', 'professional', 'enterprise']],
            [['plan'], 'default', 'value' => 'basic'],
            [['settings'], 'safe'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        // Gera código automaticamente se for uma nova empresa
        if ($insert && empty($this->code)) {
            $this->code = $this->generateUniqueCode();
        }

        return true;
    }

    /**
     * Gera um código único para a empresa
     * Formato: EMP-XXXX (onde XXXX é um número sequencial)
     */
    private function generateUniqueCode()
    {
        $prefix = 'EMP';
        $maxAttempts = 100;
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            // Obtém o último ID + 1 ou usa timestamp como fallback
            $lastCompany = self::find()->orderBy(['id' => SORT_DESC])->one();
            $nextId = $lastCompany ? $lastCompany->id + 1 : 1;
            
            // Gera código no formato EMP-0001
            $code = sprintf('%s-%04d', $prefix, $nextId + $i);
            
            // Verifica se já existe
            if (!self::find()->where(['code' => $code])->exists()) {
                return $code;
            }
        }
        
        // Fallback: usa timestamp se não conseguir gerar código único
        return sprintf('%s-%s', $prefix, substr(time(), -6));
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Código',
            'name' => 'Nome',
            'tax_id' => 'NIF',
            'email' => 'Email',
            'phone' => 'Telefone',
            'status' => 'Estado',
            'plan' => 'Plano',
            'settings' => 'Configurações',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Vehicles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVehicles()
    {
        return $this->hasMany(Vehicle::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Documents]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::class, ['company_id' => 'id']);
    }

    /**
     * Gets query for [[Files]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['company_id' => 'id']);
    }
}
