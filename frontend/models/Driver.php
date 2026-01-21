<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "users" for drivers.
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string|null $phone
 * @property string|null $license_number
 * @property string|null $license_expiry
 * @property string|null $photo
 * @property string $status
 * @property string $created_at
 * @property string|null $updated_at
 * 
 * @property Vehicle[] $vehicles
 * @property Route[] $routes
 */
class Driver extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DELETED = 'deleted'; // Para soft delete (via campo diferente se necessário)

    public $password; // Campo temporário para password

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            [['email'], 'email'],
            [['email'], 'string', 'max' => 150],
            [['name'], 'string', 'max' => 150],
            [['phone'], 'string', 'max' => 20],
            [['license_number'], 'string', 'max' => 50],
            [['license_expiry'], 'safe'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['nome', 'telefone', 'numero_carta', 'validade_carta', 'roles', 'username', 'auth_key'], 'safe'],
            // Verificar unicidade do email por empresa - filter para excluir o próprio registro ao editar
            [
                ['email'],
                'unique',
                'targetAttribute' => ['email', 'company_id'],
                'filter' => function($query) {
                    // Ao editar, excluir o próprio registro da validação
                    if (!$this->isNewRecord) {
                        $query->andWhere(['!=', 'id', $this->id]);
                    }
                },
                'message' => 'Este email já está registado na sua empresa.'
            ],
            // Password - opcional (será gerada se não fornecida)
            [['password'], 'string', 'min' => 6, 'skipOnEmpty' => true],
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
            'name' => 'Nome',
            'email' => 'Email',
            'password' => 'Palavra-passe',
            'password_hash' => 'Palavra-passe (Hash)',
            'phone' => 'Telefone',
            'license_number' => 'Número da Carta',
            'license_expiry' => 'Validade da Carta',
            'photo' => 'Foto',
            'status' => 'Estado',
            'created_at' => 'Criado em',
            'updated_at' => 'Atualizado em',
        ];
    }

    /**
     * Hash a password
     */
    public function setPassword($password)
    {
        if (!empty($password)) {
            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        }
    }

    /**
     * Options for status
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * Display status
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status] ?? '-';
    }

    /**
     * Relação com veículos atribuídos a este condutor
     * @return \yii\db\ActiveQuery
     */
    public function getVehicles()
    {
        return $this->hasMany(Vehicle::class, ['driver_id' => 'id']);
    }

    /**
     * Relação com rotas atribuídas a este condutor
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(Route::class, ['driver_id' => 'id']);
    }

    /**
     * Obter número de veículos atribuídos
     * @return int
     */
    public function getVehicleCount()
    {
        return $this->getVehicles()->count();
    }

    /**
     * Obter número de rotas atribuídas
     * @return int
     */
    public function getRouteCount()
    {
        return $this->getRoutes()->count();
    }

    /**
     * Verifica se a carta de condução está válida
     * @return bool|null null se não há data de validade
     */
    public function isLicenseValid()
    {
        if (empty($this->license_expiry)) {
            return null;
        }
        return strtotime($this->license_expiry) > time();
    }

    /**
     * Dias até a carta expirar
     * @return int|null null se não há data de validade
     */
    public function getDaysUntilLicenseExpiry()
    {
        if (empty($this->license_expiry)) {
            return null;
        }
        return ceil((strtotime($this->license_expiry) - time()) / 86400);
    }

    /**
     * Obter nome de exibição (nome ou username)
     * @return string
     */
    public function getDisplayName()
    {
        return $this->name ?: $this->username ?: 'Sem nome';
    }

    /**
     * Obter URL do avatar/foto
     * @return string|null
     */
    public function getAvatarUrl()
    {
        // Usa 'photo' que é o campo real da tabela users (não existe 'avatar')
        if (!empty($this->photo)) {
            // Se é uma URL completa (ex: Gravatar), retorna diretamente
            if (strpos($this->photo, 'http') === 0) {
                return $this->photo;
            }
            // Se é um caminho local e o arquivo existe
            if (file_exists(Yii::getAlias('@frontend/web') . $this->photo)) {
                return $this->photo;
            }
        }
        return null;
    }

    /**
     * Check if driver is available (active and with valid license)
     * @return bool
     */
    public function isAvailable()
    {
        if ($this->status != self::STATUS_ACTIVE) {
            return false;
        }
        $licenseValid = $this->isLicenseValid();
        // If no expiry date, consider available
        return $licenseValid === null || $licenseValid === true;
    }

    // Alias getters for legacy fields
    public function getNome() { return $this->name; }
    public function setNome($v) { $this->name = $v; }
    public function getTelefone() { return $this->phone; }
    public function setTelefone($v) { $this->phone = $v; }
    public function getNumero_carta() { return $this->license_number; }
    public function setNumero_carta($v) { $this->license_number = $v; }
    public function getValidade_carta() { return $this->license_expiry; }
    public function setValidade_carta($v) { $this->license_expiry = $v; }
    public function getStatus() { return $this->status; }
    public function setStatus($v) { $this->status = $v; }
}
