<?php

namespace dashboard\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\ContainerSurveys;

/**
 * ContainerSurveysSearch represents the model behind the search form of `dashboard\models\ContainerSurveys`.
 */
class ContainerSurveysSearch extends ContainerSurveys
{
    /**
     * {@inheritdoc}
     */
    public $globalSearch;
    public function rules()
    {
        return [
            [['survey_id', 'visit_id', 'created_at', 'updated_at'], 'integer'],
            [['survey_date', 'surveyor_name', 'approval_status'], 'safe'],
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
        $query = ContainerSurveys::find();

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
            'survey_id' => $this->globalSearch,
            'visit_id' => $this->globalSearch,
            'survey_date' => $this->globalSearch,
            'created_at' => $this->globalSearch,
            'updated_at' => $this->globalSearch,
        ]);

        $query->orFilterWhere(['like', 'surveyor_name', $this->globalSearch])
            ->orFilterWhere(['like', 'approval_status', $this->globalSearch]);
        }else{
                $query->andFilterWhere([
            'survey_id' => $this->survey_id,
            'visit_id' => $this->visit_id,
            'survey_date' => $this->survey_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'surveyor_name', $this->surveyor_name])
            ->andFilterWhere(['like', 'approval_status', $this->approval_status]);
        }
        return $dataProvider;
    }
}
