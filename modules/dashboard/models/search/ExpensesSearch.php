<?php

namespace dashboard\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use dashboard\models\Expenses;

/**
 * ExpensesSearch represents the search model for `dashboard\models\Expenses`.
 */
class ExpensesSearch extends Expenses
{
    public $globalSearch;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['expense_id', 'category_id', 'is_deleted', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number'],
            [['expense_date', 'reference_no', 'payment_method', 'description', 'globalSearch'], 'safe'],
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
        $query = Expenses::find()->joinWith('category');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['expense_date' => SORT_DESC]],
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Global search logic (compact)
        if ($this->globalSearch) {
            $query->andFilterWhere(['or',
                ['like', 'expense_categories.category_name', $this->globalSearch],
                ['like', 'reference_no', $this->globalSearch],
                ['like', 'expenses.description', $this->globalSearch],
                ['like', 'payment_method', $this->globalSearch],
            ]);
        } else {
            $query->andFilterWhere([
                'expense_id' => $this->expense_id,
                'category_id' => $this->category_id,
                'amount' => $this->amount,
                'expense_date' => $this->expense_date,
            ]);

            $query->andFilterWhere(['like', 'reference_no', $this->reference_no])
                ->andFilterWhere(['like', 'payment_method', $this->payment_method])
                ->andFilterWhere(['like', 'description', $this->description]);
        }

        return $dataProvider;
    }
}
