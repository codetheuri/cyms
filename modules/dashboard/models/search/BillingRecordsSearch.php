<?php

namespace dashboard\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\BillingRecords;

/**
 * BillingRecordsSearch represents the model behind the search form of `dashboard\models\BillingRecords`.
 */
class BillingRecordsSearch extends BillingRecords
{
    /**
     * {@inheritdoc}
     */
    public $globalSearch;
    public function rules()
    {
        return [
            [['bill_id', 'visit_id', 'storage_days', 'created_at', 'updated_at'], 'integer'],
            [['tariff_rate', 'grand_total'], 'number'],
            [['status', ], 'safe'],
            ['globalSearch', 'safe']
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BillingRecords::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [ 'defaultPageSize' => \Yii::$app->params['defaultPageSize'], 'pageSizeLimit' => [1, \Yii::$app->params['pageSizeLimit']]],
            'sort'=> ['defaultOrder' => ['created_at'=>SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        if(isset($this->globalSearch)){
                $query->orFilterWhere([
            'bill_id' => $this->globalSearch,
            'visit_id' => $this->globalSearch,
            'storage_days' => $this->globalSearch,
            'tariff_rate' => $this->globalSearch,
            'grand_total' => $this->globalSearch,
            'created_at' => $this->globalSearch,
            'updated_at' => $this->globalSearch,
        ]);

        $query->orFilterWhere(['like', 'status', $this->globalSearch]);
            // ->orFilterWhere(['like', 'receipt_no', $this->globalSearch]);
        }else{
                $query->andFilterWhere([
            'bill_id' => $this->bill_id,
            'visit_id' => $this->visit_id,
            'storage_days' => $this->storage_days,
            'tariff_rate' => $this->tariff_rate,
            'grand_total' => $this->grand_total,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status]);
            // ->andFilterWhere(['like',  $this->receipt_no]);
        }
        return $dataProvider;
    }
}
