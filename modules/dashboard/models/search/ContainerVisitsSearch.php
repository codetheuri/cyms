<?php

namespace dashboard\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\ContainerVisits;

/**
 * ContainerVisitsSearch represents the model behind the search form of `dashboard\models\ContainerVisits`.
 */
class ContainerVisitsSearch extends ContainerVisits
{
    /**
     * {@inheritdoc}
     */
    public $globalSearch;

    public function rules()
    {
        return [
            // Integers
            [['visit_id', 'yard_clerk_in', 'yard_clerk_out', 'storage_days', 'created_at', 'updated_at', 'created_by'], 'integer'],
            
            // Strings / Safe Attributes
            [[
                'container_number', 'status', 'globalSearch',
                
                // Gate IN Fields
                'ticket_no_in', 'date_in', 'time_in', 
                'vehicle_reg_no_in', 'truck_type_in', 'trailer_reg_no_in', 
                'seal_number_in', 'truck_owner_name_in', 'truck_owner_contact_in', 
                'driver_name_in', 'driver_id_in',
                
                // Gate OUT Fields
                'ticket_no_out', 'date_out', 'time_out', 
                'vehicle_reg_no_out', 'truck_type_out', 'trailer_reg_no_out', 
                'seal_number_out', 'truck_owner_name_out', 'truck_owner_contact_out', 
                'driver_name_out', 'driver_id_out', 'destination'
            ], 'safe'],
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
        $query = ContainerVisits::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => \Yii::$app->params['defaultPageSize'] ?? 20, 
                'pageSizeLimit' => [1, \Yii::$app->params['pageSizeLimit'] ?? 50]
            ],
            'sort'=> ['defaultOrder' => ['created_at' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // --- 1. GLOBAL SEARCH (Search Bar) ---
        if (isset($this->globalSearch) && $this->globalSearch !== '') {
            $query->orFilterWhere(['like', 'container_number', $this->globalSearch])
                ->orFilterWhere(['like', 'ticket_no_in', $this->globalSearch])
                ->orFilterWhere(['like', 'ticket_no_out', $this->globalSearch])
                ->orFilterWhere(['like', 'vehicle_reg_no_in', $this->globalSearch])
                ->orFilterWhere(['like', 'vehicle_reg_no_out', $this->globalSearch])
                ->orFilterWhere(['like', 'driver_name_in', $this->globalSearch])
                ->orFilterWhere(['like', 'driver_name_out', $this->globalSearch])
                ->orFilterWhere(['like', 'destination', $this->globalSearch]);
                
        } else {
            // --- 2. COLUMN SPECIFIC FILTERING ---
            
            // Exact matches for IDs and Status (if array passed)
            $query->andFilterWhere([
                'visit_id' => $this->visit_id,
                'yard_clerk_in' => $this->yard_clerk_in,
                'yard_clerk_out' => $this->yard_clerk_out,
                'storage_days' => $this->storage_days,
                'date_in' => $this->date_in,
                'date_out' => $this->date_out,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'created_by' => $this->created_by,
            ]);

            // If status is an array (from Controller logic), use IN condition, else use LIKE
            if (is_array($this->status)) {
                $query->andFilterWhere(['status' => $this->status]);
            } else {
                $query->andFilterWhere(['like', 'status', $this->status]);
            }

            // String partial matches
            $query->andFilterWhere(['like', 'container_number', $this->container_number])
                
                // Gate IN
                ->andFilterWhere(['like', 'ticket_no_in', $this->ticket_no_in])
                ->andFilterWhere(['like', 'vehicle_reg_no_in', $this->vehicle_reg_no_in])
                ->andFilterWhere(['like', 'driver_name_in', $this->driver_name_in])
                ->andFilterWhere(['like', 'driver_id_in', $this->driver_id_in])
                ->andFilterWhere(['like', 'truck_owner_name_in', $this->truck_owner_name_in])
                
                // Gate OUT
                ->andFilterWhere(['like', 'ticket_no_out', $this->ticket_no_out])
                ->andFilterWhere(['like', 'vehicle_reg_no_out', $this->vehicle_reg_no_out])
                ->andFilterWhere(['like', 'driver_name_out', $this->driver_name_out])
                ->andFilterWhere(['like', 'destination', $this->destination]);
        }

        return $dataProvider;
    }
}