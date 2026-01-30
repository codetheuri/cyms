<?php

namespace dashboard\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\MasterContainerOwners;

class MasterContainerOwnersSearch extends MasterContainerOwners
{
    public $globalSearch;

    public function rules()
    {
        return [
            [['owner_id', 'created_at'], 'integer'],
            [['owner_name', 'owner_contact', 'owner_email', 'globalSearch'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = MasterContainerOwners::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->globalSearch) {
            $query->orFilterWhere(['like', 'owner_name', $this->globalSearch])
                  ->orFilterWhere(['like', 'owner_contact', $this->globalSearch])
                  ->orFilterWhere(['like', 'owner_email', $this->globalSearch]);
        }

        return $dataProvider;
    }
}