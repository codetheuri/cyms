<?php

namespace helpers\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use helpers\models\AuditTrail;
use Yii;

/**
 * AuditTrailSearch represents the search model for `helpers\models\AuditTrail`.
 */
class AuditTrailSearch extends AuditTrail
{
    /**
     * @var string Global search query
     */
    public $q;
    public $start_date;
    public $end_date;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'audit_time', 'memory_max', 'is_deleted', 'status', 'created_at', 'updated_at'], 'integer'],
            [['model_name', 'operation', 'request_method', 'field_name', 'old_value', 'new_value', 'user_id', 'request_route', 'headers', 'query_params', 'body_params', 'raw_body', 'url', 'ip_address', 'user_agent', 'q', 'start_date', 'end_date'], 'safe'],
            [['duration'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function search($params)
    {
        $query = AuditTrail::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['audit_time' => SORT_DESC]],
            'pagination' => [
                'pageSize' => Yii::$app->request->get('per-page', 25),
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        if (!empty($this->start_date)) {
            $query->andWhere(['>=', 'audit_time', strtotime($this->start_date)]);
        }
        if (!empty($this->end_date)) {
            $query->andWhere(['<=', 'audit_time', strtotime($this->end_date . ' 23:59:59')]);
        }

        if (!empty($this->q)) {
            $query->andFilterWhere(['or',
                ['like', 'model_name', $this->q],
                ['like', 'operation', $this->q],
                ['like', 'field_name', $this->q],
                ['like', 'old_value', $this->q],
                ['like', 'new_value', $this->q],
                ['like', 'user_id', $this->q],
                ['like', 'ip_address', $this->q],
                ['like', 'url', $this->q],
            ]);
        } else {
            $query->andFilterWhere([
                'id' => $this->id,
                'audit_time' => $this->audit_time,
                'user_id' => $this->user_id,
            ]);

            $query->andFilterWhere(['like', 'model_name', $this->model_name])
                ->andFilterWhere(['like', 'operation', $this->operation])
                ->andFilterWhere(['like', 'field_name', $this->field_name])
                ->andFilterWhere(['like', 'ip_address', $this->ip_address]);
        }

        return $dataProvider;
    }
}
