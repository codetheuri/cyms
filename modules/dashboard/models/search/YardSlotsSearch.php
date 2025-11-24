<?php

namespace dashboard\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\YardSlots;

/**
 * YardSlotsSearch represents the model behind the search form of `dashboard\models\YardSlots`.
 */
class YardSlotsSearch extends YardSlots
{
    /**
     * {@inheritdoc}
     */
    public $globalSearch;
    public function rules()
    {
        return [
            [['slot_id', 'row', 'bay', 'tier', 'current_visit_id'], 'integer'],
            [['block', 'slot_name'], 'safe'],
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
        $query = YardSlots::find();

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
            'slot_id' => $this->globalSearch,
            'row' => $this->globalSearch,
            'bay' => $this->globalSearch,
            'tier' => $this->globalSearch,
            'current_visit_id' => $this->globalSearch,
        ]);

        $query->orFilterWhere(['like', 'block', $this->globalSearch])
            ->orFilterWhere(['like', 'slot_name', $this->globalSearch]);
        }else{
                $query->andFilterWhere([
            'slot_id' => $this->slot_id,
            'row' => $this->row,
            'bay' => $this->bay,
            'tier' => $this->tier,
            'current_visit_id' => $this->current_visit_id,
        ]);

        $query->andFilterWhere(['like', 'block', $this->block])
            ->andFilterWhere(['like', 'slot_name', $this->slot_name]);
        }
        return $dataProvider;
    }
}
