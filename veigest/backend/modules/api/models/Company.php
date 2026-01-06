<?php

namespace backend\modules\api\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Company API model
 *
 * @property integer $id
 * @property string $code
 * @property string $nome
 * @property string $name
 * @property string $email
 * @property string $telefone
 * @property string $phone
 * @property string $nif
 * @property string $tax_id
 * @property string $morada
 * @property string $address
 * @property string $cidade
 * @property string $city
 * @property string $codigo_postal
 * @property string $postal_code
 * @property string $pais
 * @property string $country
 * @property string $estado
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
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
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nome', 'email'], 'required'],
            [['nome', 'name'], 'string', 'max' => 150],
            [['email'], 'email'],
            [['telefone', 'phone'], 'string', 'max' => 20],
            [['nif', 'tax_id'], 'string', 'max' => 50],
            [['morada', 'address', 'cidade', 'city'], 'string', 'max' => 200],
            [['codigo_postal', 'postal_code'], 'string', 'max' => 20],
            [['pais', 'country'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 10],
            [['estado', 'status'], 'in', 'range' => ['ativo', 'inativo', 'active', 'inactive']],
            [['estado', 'status'], 'default', 'value' => 'ativo'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Código',
            'nome' => 'Nome',
            'name' => 'Name',
            'email' => 'Email',
            'telefone' => 'Telefone',
            'phone' => 'Phone',
            'nif' => 'NIF',
            'tax_id' => 'Tax ID',
            'morada' => 'Morada',
            'address' => 'Address',
            'cidade' => 'Cidade',
            'city' => 'City',
            'codigo_postal' => 'Código Postal',
            'postal_code' => 'Postal Code',
            'pais' => 'País',
            'country' => 'Country',
            'estado' => 'Estado',
            'status' => 'Status',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id',
            'code',
            'nome',
            'name' => 'nome', // Alias para compatibilidade
            'email',
            'telefone',
            'phone' => 'telefone', // Alias para compatibilidade
            'nif',
            'tax_id' => 'nif', // Alias para compatibilidade
            'morada',
            'address' => 'morada', // Alias para compatibilidade
            'cidade',
            'city' => 'cidade', // Alias para compatibilidade
            'codigo_postal',
            'postal_code' => 'codigo_postal', // Alias para compatibilidade
            'pais',
            'country' => 'pais', // Alias para compatibilidade
            'estado',
            'status' => 'estado', // Alias para compatibilidade
            'created_at',
            'updated_at',
        ];
    }

    /**
     * Get vehicles relationship
     */
    public function getVehicles()
    {
        return $this->hasMany(\backend\modules\api\models\Vehicle::class, ['company_id' => 'id']);
    }

    /**
     * Get users relationship
     */
    public function getUsers()
    {
        return $this->hasMany(\common\models\User::class, ['company_id' => 'id']);
    }

    /**
     * Get active vehicles count
     */
    public function getActiveVehiclesCount()
    {
        return $this->getVehicles()
            ->where(['status' => 'active'])
            ->count();
    }

    /**
     * Get total users count
     */
    public function getTotalUsersCount()
    {
        return $this->getUsers()
            ->where(['estado' => 'ativo'])
            ->count();
    }
}
