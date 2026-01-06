# ➕ Guia: Adicionar CRUD

## Visão Geral

Este guia mostra como adicionar uma nova funcionalidade CRUD (Create, Read, Update, Delete) ao VeiGest, passo a passo.

**Exemplo**: Vamos criar o CRUD para **Fornecedores** (`Supplier`).

---

## Passo 1: Criar Migration

```bash
php yii migrate/create create_supplier_table
```

### Ficheiro: `console/migrations/m{timestamp}_create_supplier_table.php`

```php
<?php
use yii\db\Migration;

class m240120_100000_create_supplier_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%supplier}}', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'nif' => $this->string(20),
            'email' => $this->string(255),
            'phone' => $this->string(20),
            'address' => $this->text(),
            'category' => $this->string(50)->defaultValue('general'),
            'status' => $this->string(20)->defaultValue('active'),
            'notes' => $this->text(),
            'created_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
        
        $this->createIndex('idx-supplier-company', '{{%supplier}}', 'company_id');
        $this->createIndex('idx-supplier-status', '{{%supplier}}', 'status');
        
        $this->addForeignKey(
            'fk-supplier-company',
            '{{%supplier}}',
            'company_id',
            '{{%company}}',
            'id',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-supplier-company', '{{%supplier}}');
        $this->dropTable('{{%supplier}}');
    }
}
```

### Aplicar Migration

```bash
php yii migrate
```

---

## Passo 2: Criar Model

### Ficheiro: `common/models/Supplier.php`

```php
<?php
namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Supplier model
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $nif
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $address
 * @property string $category
 * @property string $status
 * @property string|null $notes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Company $company
 */
class Supplier extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_PARTS = 'parts';
    const CATEGORY_FUEL = 'fuel';
    const CATEGORY_TIRES = 'tires';
    const CATEGORY_WORKSHOP = 'workshop';
    
    public static function tableName()
    {
        return '{{%supplier}}';
    }
    
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
    
    public function rules()
    {
        return [
            [['company_id', 'name'], 'required'],
            [['company_id'], 'integer'],
            [['address', 'notes'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['nif', 'phone'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['category'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 20],
            [['status'], 'in', 'range' => array_keys(self::getStatusList())],
            [['category'], 'in', 'range' => array_keys(self::getCategoryList())],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['category'], 'default', 'value' => self::CATEGORY_GENERAL],
            [['company_id'], 'exist', 'targetClass' => Company::class, 'targetAttribute' => ['company_id' => 'id']],
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Empresa',
            'name' => 'Nome',
            'nif' => 'NIF',
            'email' => 'Email',
            'phone' => 'Telefone',
            'address' => 'Morada',
            'category' => 'Categoria',
            'status' => 'Estado',
            'notes' => 'Observações',
            'created_at' => 'Criado Em',
            'updated_at' => 'Actualizado Em',
        ];
    }
    
    // Relações
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }
    
    // Métodos estáticos
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
        ];
    }
    
    public static function getCategoryList()
    {
        return [
            self::CATEGORY_GENERAL => 'Geral',
            self::CATEGORY_PARTS => 'Peças',
            self::CATEGORY_FUEL => 'Combustível',
            self::CATEGORY_TIRES => 'Pneus',
            self::CATEGORY_WORKSHOP => 'Oficina',
        ];
    }
    
    // Getters
    public function getStatusLabel()
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }
    
    public function getCategoryLabel()
    {
        return self::getCategoryList()[$this->category] ?? $this->category;
    }
}
```

---

## Passo 3: Criar SearchModel (para filtros)

### Ficheiro: `frontend/models/SupplierSearch.php`

```php
<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Supplier;

class SupplierSearch extends Supplier
{
    public function rules()
    {
        return [
            [['id', 'company_id'], 'integer'],
            [['name', 'nif', 'email', 'phone', 'category', 'status'], 'safe'],
        ];
    }
    
    public function scenarios()
    {
        return Model::scenarios();
    }
    
    public function search($params)
    {
        // Filtrar sempre por empresa do utilizador
        $companyId = Yii::$app->user->identity->company_id;
        
        $query = Supplier::find()->where(['company_id' => $companyId]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        
        $this->load($params);
        
        if (!$this->validate()) {
            return $dataProvider;
        }
        
        // Filtros
        $query->andFilterWhere(['like', 'name', $this->name])
              ->andFilterWhere(['like', 'nif', $this->nif])
              ->andFilterWhere(['like', 'email', $this->email])
              ->andFilterWhere(['category' => $this->category])
              ->andFilterWhere(['status' => $this->status]);
        
        return $dataProvider;
    }
}
```

---

## Passo 4: Criar Controller

### Ficheiro: `frontend/controllers/SupplierController.php`

```php
<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\Supplier;
use frontend\models\SupplierSearch;

class SupplierController extends Controller
{
    public $layout = 'dashboard';
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
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
     * Lista de fornecedores
     */
    public function actionIndex()
    {
        $searchModel = new SupplierSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Ver detalhes
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    
    /**
     * Criar novo
     */
    public function actionCreate()
    {
        $model = new Supplier();
        $model->company_id = Yii::$app->user->identity->company_id;
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Fornecedor criado com sucesso.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    
    /**
     * Editar
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Fornecedor actualizado com sucesso.');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    
    /**
     * Eliminar
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Fornecedor eliminado.');
        return $this->redirect(['index']);
    }
    
    /**
     * Encontrar model com validação de empresa
     */
    protected function findModel($id)
    {
        $companyId = Yii::$app->user->identity->company_id;
        
        $model = Supplier::findOne([
            'id' => $id,
            'company_id' => $companyId,
        ]);
        
        if ($model === null) {
            throw new NotFoundHttpException('Fornecedor não encontrado.');
        }
        
        return $model;
    }
}
```

---

## Passo 5: Criar Views

### 5.1 Lista (`frontend/views/supplier/index.php`)

```php
<?php
use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Supplier;

$this->title = 'Fornecedores';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="supplier-index">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= Html::encode($this->title) ?></h1>
        <?= Html::a('+ Novo Fornecedor', ['create'], [
            'class' => 'bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700'
        ]) ?>
    </div>
    
    <!-- Tabela -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'min-w-full divide-y divide-gray-200'],
            'columns' => [
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function($model) {
                        return Html::a(Html::encode($model->name), ['view', 'id' => $model->id], [
                            'class' => 'text-blue-600 hover:underline font-medium'
                        ]);
                    },
                ],
                'nif',
                'email:email',
                'phone',
                [
                    'attribute' => 'category',
                    'value' => function($model) {
                        return $model->getCategoryLabel();
                    },
                    'filter' => Supplier::getCategoryList(),
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function($model) {
                        $class = $model->status === 'active' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-gray-100 text-gray-800';
                        return Html::tag('span', $model->getStatusLabel(), [
                            'class' => "px-2 py-1 rounded-full text-xs $class"
                        ]);
                    },
                    'filter' => Supplier::getStatusList(),
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete}',
                    'contentOptions' => ['class' => 'text-right'],
                ],
            ],
        ]) ?>
    </div>
</div>
```

### 5.2 Formulário (`frontend/views/supplier/_form.php`)

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Supplier;
?>

<div class="supplier-form">
    <?php $form = ActiveForm::begin([
        'options' => ['class' => 'space-y-6'],
    ]); ?>
    
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?= $form->field($model, 'name')->textInput([
                'maxlength' => true,
                'class' => 'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500',
                'placeholder' => 'Nome do fornecedor'
            ]) ?>
            
            <?= $form->field($model, 'nif')->textInput([
                'maxlength' => true,
                'class' => 'w-full px-4 py-2 border rounded-lg',
                'placeholder' => 'NIF'
            ]) ?>
            
            <?= $form->field($model, 'email')->textInput([
                'type' => 'email',
                'class' => 'w-full px-4 py-2 border rounded-lg',
                'placeholder' => 'email@exemplo.com'
            ]) ?>
            
            <?= $form->field($model, 'phone')->textInput([
                'class' => 'w-full px-4 py-2 border rounded-lg',
                'placeholder' => '+351 XXX XXX XXX'
            ]) ?>
            
            <?= $form->field($model, 'category')->dropDownList(
                Supplier::getCategoryList(),
                ['class' => 'w-full px-4 py-2 border rounded-lg']
            ) ?>
            
            <?= $form->field($model, 'status')->dropDownList(
                Supplier::getStatusList(),
                ['class' => 'w-full px-4 py-2 border rounded-lg']
            ) ?>
        </div>
        
        <div class="mt-6">
            <?= $form->field($model, 'address')->textarea([
                'rows' => 3,
                'class' => 'w-full px-4 py-2 border rounded-lg',
                'placeholder' => 'Morada completa'
            ]) ?>
        </div>
        
        <div class="mt-6">
            <?= $form->field($model, 'notes')->textarea([
                'rows' => 3,
                'class' => 'w-full px-4 py-2 border rounded-lg',
                'placeholder' => 'Observações...'
            ]) ?>
        </div>
    </div>
    
    <div class="flex justify-end gap-3">
        <?= Html::a('Cancelar', ['index'], [
            'class' => 'px-4 py-2 border rounded-lg hover:bg-gray-50'
        ]) ?>
        <?= Html::submitButton($model->isNewRecord ? 'Criar' : 'Guardar', [
            'class' => 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700'
        ]) ?>
    </div>
    
    <?php ActiveForm::end(); ?>
</div>
```

### 5.3 Criar (`frontend/views/supplier/create.php`)

```php
<?php
use yii\helpers\Html;

$this->title = 'Novo Fornecedor';
$this->params['breadcrumbs'][] = ['label' => 'Fornecedores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="supplier-create">
    <h1 class="text-2xl font-bold mb-6"><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
```

### 5.4 Editar (`frontend/views/supplier/update.php`)

```php
<?php
use yii\helpers\Html;

$this->title = 'Editar: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Fornecedores', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Editar';
?>

<div class="supplier-update">
    <h1 class="text-2xl font-bold mb-6"><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
```

### 5.5 Ver (`frontend/views/supplier/view.php`)

```php
<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Fornecedores', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="supplier-view">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= Html::encode($this->title) ?></h1>
        <div class="flex gap-2">
            <?= Html::a('Editar', ['update', 'id' => $model->id], [
                'class' => 'px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700'
            ]) ?>
            <?= Html::a('Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700',
                'data' => [
                    'confirm' => 'Tem a certeza que deseja eliminar este fornecedor?',
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>
    
    <!-- Detalhes -->
    <div class="bg-white rounded-lg shadow">
        <?= DetailView::widget([
            'model' => $model,
            'options' => ['class' => 'table-auto w-full'],
            'attributes' => [
                'id',
                'name',
                'nif',
                'email:email',
                'phone',
                'address:ntext',
                [
                    'attribute' => 'category',
                    'value' => $model->getCategoryLabel(),
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function() use ($model) {
                        $class = $model->status === 'active' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-gray-100 text-gray-800';
                        return Html::tag('span', $model->getStatusLabel(), [
                            'class' => "px-2 py-1 rounded-full text-xs $class"
                        ]);
                    },
                ],
                'notes:ntext',
                'created_at:datetime',
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>
```

---

## Passo 6: Adicionar ao Menu (Opcional)

### Em `frontend/views/layouts/dashboard.php`

```php
<!-- No menu lateral -->
<?= $this->render('_menu-item', [
    'icon' => 'supplier',
    'label' => 'Fornecedores',
    'url' => ['supplier/index'],
    'active' => strpos($currentRoute, 'supplier') !== false,
]) ?>
```

---

## Resumo dos Ficheiros Criados

```
console/migrations/
└── m240120_100000_create_supplier_table.php

common/models/
└── Supplier.php

frontend/
├── controllers/
│   └── SupplierController.php
├── models/
│   └── SupplierSearch.php
└── views/supplier/
    ├── index.php
    ├── view.php
    ├── create.php
    ├── update.php
    └── _form.php
```

---

## Próximos Passos

- [Adicionar Endpoint API](adicionar-endpoint-api.md)
- [Testes](testes.md)
