<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Modelo para histórico de alterações de perfil.
 * 
 * RF-FO-003.5: Histórico de alterações
 *
 * @property int $id
 * @property int $user_id
 * @property string $field_name
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string $change_type
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string $created_at
 *
 * @property User $user
 */
class ProfileHistory extends ActiveRecord
{
    const TYPE_UPDATE = 'update';
    const TYPE_PASSWORD = 'password';
    const TYPE_PHOTO = 'photo';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%profile_history}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'field_name'], 'required'],
            [['user_id'], 'integer'],
            [['old_value', 'new_value'], 'string'],
            [['field_name'], 'string', 'max' => 50],
            [['change_type'], 'in', 'range' => [self::TYPE_UPDATE, self::TYPE_PASSWORD, self::TYPE_PHOTO]],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
            [['created_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Utilizador',
            'field_name' => 'Campo Alterado',
            'old_value' => 'Valor Anterior',
            'new_value' => 'Novo Valor',
            'change_type' => 'Tipo de Alteração',
            'ip_address' => 'Endereço IP',
            'user_agent' => 'Dispositivo',
            'created_at' => 'Data da Alteração',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Regista uma alteração no histórico
     * 
     * @param int $userId
     * @param string $fieldName
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param string $changeType
     * @return bool
     */
    public static function logChange($userId, $fieldName, $oldValue, $newValue, $changeType = self::TYPE_UPDATE)
    {
        $history = new self();
        $history->user_id = $userId;
        $history->field_name = $fieldName;
        $history->old_value = is_array($oldValue) ? json_encode($oldValue) : (string)$oldValue;
        $history->new_value = is_array($newValue) ? json_encode($newValue) : (string)$newValue;
        $history->change_type = $changeType;
        $history->ip_address = Yii::$app->request->userIP ?? null;
        $history->user_agent = isset(Yii::$app->request->userAgent) ? substr(Yii::$app->request->userAgent, 0, 255) : null;
        
        return $history->save();
    }

    /**
     * Retorna label traduzido para o campo
     * 
     * @return string
     */
    public function getFieldLabel()
    {
        $labels = [
            'name' => 'Nome',
            'email' => 'Email',
            'phone' => 'Telefone',
            'photo' => 'Foto de Perfil',
            'password' => 'Palavra-passe',
            'license_number' => 'Número da Carta',
            'license_expiry' => 'Validade da Carta',
        ];

        return $labels[$this->field_name] ?? $this->field_name;
    }

    /**
     * Retorna label traduzido para o tipo de alteração
     * 
     * @return string
     */
    public function getChangeTypeLabel()
    {
        $labels = [
            self::TYPE_UPDATE => 'Atualização de Dados',
            self::TYPE_PASSWORD => 'Alteração de Palavra-passe',
            self::TYPE_PHOTO => 'Atualização de Foto',
        ];

        return $labels[$this->change_type] ?? $this->change_type;
    }

    /**
     * Retorna ícone para o tipo de alteração
     * 
     * @return string
     */
    public function getChangeTypeIcon()
    {
        $icons = [
            self::TYPE_UPDATE => 'fas fa-edit',
            self::TYPE_PASSWORD => 'fas fa-key',
            self::TYPE_PHOTO => 'fas fa-camera',
        ];

        return $icons[$this->change_type] ?? 'fas fa-history';
    }

    /**
     * Retorna cor CSS para o tipo de alteração
     * 
     * @return string
     */
    public function getChangeTypeColor()
    {
        $colors = [
            self::TYPE_UPDATE => 'info',
            self::TYPE_PASSWORD => 'warning',
            self::TYPE_PHOTO => 'success',
        ];

        return $colors[$this->change_type] ?? 'secondary';
    }
}
