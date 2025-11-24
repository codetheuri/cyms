<?php

namespace dashboard\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\MasterContainerTypes;

/**
 * MasterContainerTypesSearch represents the model behind the search form of `dashboard\models\MasterContainerTypes`.
 */
class MasterContainerTypesSearch extends MasterContainerTypes
{
    /**
     * {@inheritdoc}
     */
    public $globalSearch;
    public function rules()
    {
        return [
            [['type_id', 'size'], 'integer'],
            [['iso_code', 'type_group', 'description'], 'safe'],
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
        $query = MasterContainerTypes::find();

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
            'type_id' => $this->globalSearch,
            'size' => $this->globalSearch,
        ]);

        $query->orFilterWhere(['like', 'iso_code', $this->globalSearch])
            ->orFilterWhere(['like', 'type_group', $this->globalSearch])
            ->orFilterWhere(['like', 'description', $this->globalSearch]);
        }else{
                $query->andFilterWhere([
            'type_id' => $this->type_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'iso_code', $this->iso_code])
            ->andFilterWhere(['like', 'type_group', $this->type_group])
            ->andFilterWhere(['like', 'description', $this->description]);
        }
        return $dataProvider;
    }
}
