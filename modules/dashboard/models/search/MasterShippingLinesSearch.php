<?php

namespace dashboard\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\MasterShippingLines;

/**
 * MasterShippingLinesSearch represents the model behind the search form of `dashboard\models\MasterShippingLines`.
 */
class MasterShippingLinesSearch extends MasterShippingLines
{
    /**
     * {@inheritdoc}
     */
    public $globalSearch;
    public function rules()
    {
        return [
            [['line_id', 'created_at', 'updated_at'], 'integer'],
            [['line_code', 'line_name', 'contact_email'], 'safe'],
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
        $query = MasterShippingLines::find();

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
            'line_id' => $this->globalSearch,
            'created_at' => $this->globalSearch,
            'updated_at' => $this->globalSearch,
        ]);

        $query->orFilterWhere(['like', 'line_code', $this->globalSearch])
            ->orFilterWhere(['like', 'line_name', $this->globalSearch])
            ->orFilterWhere(['like', 'contact_email', $this->globalSearch]);
        }else{
                $query->andFilterWhere([
            'line_id' => $this->line_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'line_code', $this->line_code])
            ->andFilterWhere(['like', 'line_name', $this->line_name])
            ->andFilterWhere(['like', 'contact_email', $this->contact_email]);
        }
        return $dataProvider;
    }
}
