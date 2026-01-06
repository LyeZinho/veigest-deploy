<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Document;

/**
 * DocumentSearch represents the model behind the search form of `common\models\Document`.
 */
class DocumentSearch extends Document
{
    /**
     * Atributo para pesquisa textual
     */
    public $searchText;

    /**
     * Atributo para filtro de status visual
     */
    public $statusFilter;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'file_id', 'vehicle_id', 'driver_id'], 'integer'],
            [['type', 'status', 'expiry_date', 'notes', 'created_at', 'updated_at', 'searchText', 'statusFilter'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $companyId = Yii::$app->user->identity->company_id ?? null;

        $query = Document::find()
            ->joinWith(['file', 'vehicle', 'driver'])
            ->where(['documents.company_id' => $companyId]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
                'attributes' => [
                    'id',
                    'type',
                    'status',
                    'expiry_date',
                    'created_at',
                    'vehicle_id',
                    'driver_id',
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Filtros grid
        $query->andFilterWhere([
            'documents.id' => $this->id,
            'documents.vehicle_id' => $this->vehicle_id,
            'documents.driver_id' => $this->driver_id,
            'documents.type' => $this->type,
        ]);

        // Filtro de status visual (válido, próximo vencimento, expirado)
        if (!empty($this->statusFilter)) {
            switch ($this->statusFilter) {
                case 'valid':
                    $query->andWhere(['documents.status' => Document::STATUS_VALID])
                          ->andWhere(['OR', 
                              ['documents.expiry_date' => null], 
                              ['>', 'documents.expiry_date', new \yii\db\Expression('DATE_ADD(CURDATE(), INTERVAL 30 DAY)')]
                          ]);
                    break;
                case 'expiring':
                    $query->andWhere(['documents.status' => Document::STATUS_VALID])
                          ->andWhere(['<=', 'documents.expiry_date', new \yii\db\Expression('DATE_ADD(CURDATE(), INTERVAL 30 DAY)')])
                          ->andWhere(['>=', 'documents.expiry_date', new \yii\db\Expression('CURDATE()')]);
                    break;
                case 'expired':
                    $query->andWhere(['<', 'documents.expiry_date', new \yii\db\Expression('CURDATE()')]);
                    break;
            }
        } elseif (!empty($this->status)) {
            $query->andFilterWhere(['documents.status' => $this->status]);
        }

        // Pesquisa textual (nome do ficheiro, notas, matrícula)
        if (!empty($this->searchText)) {
            $query->andWhere(['OR',
                ['LIKE', 'files.original_name', $this->searchText],
                ['LIKE', 'documents.notes', $this->searchText],
                ['LIKE', 'vehicles.plate', $this->searchText],
                ['LIKE', 'users.name', $this->searchText],
            ]);
        }

        // Filtro de data
        if (!empty($this->expiry_date)) {
            $query->andFilterWhere(['documents.expiry_date' => $this->expiry_date]);
        }

        return $dataProvider;
    }

    /**
     * Retorna opções para o filtro de status
     * 
     * @return array
     */
    public static function getStatusFilterOptions()
    {
        return [
            '' => 'Todos os Estados',
            'valid' => 'Válido',
            'expiring' => 'Próximo do Vencimento',
            'expired' => 'Expirado',
        ];
    }
}
