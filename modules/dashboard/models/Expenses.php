<?php

namespace dashboard\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "expenses".
 *
 * @property int $expense_id
 * @property int $category_id
 * @property float $amount
 * @property string $expense_date
 * @property string|null $reference_no
 * @property string $payment_method
 * @property string|null $description
 * @property int|null $recorded_by
 * @property int $is_deleted
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property ExpenseCategories $category
 */
class Expenses extends BaseModel
{
    public static function tableName()
    {
        return '{{%expenses}}';
    }

    public function rules()
    {
        return [
            [['category_id', 'amount', 'expense_date', 'payment_method'], 'required'],
            [['category_id', 'recorded_by', 'is_deleted', 'status', 'created_at', 'updated_at'], 'integer'],
            [['amount'], 'number', 'min' => 0.01],
            [['expense_date'], 'date', 'format' => 'php:Y-m-d'],
            [['description'], 'string'],
            [['reference_no'], 'string', 'max' => 100],
            [['payment_method'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'expense_id' => 'Expense ID',
            'category_id' => 'Category',
            'amount' => 'Amount Spent',
            'expense_date' => 'Date of Expense',
            'reference_no' => 'Reference (Receipt No)',
            'payment_method' => 'Payment Method',
            'description' => 'Specific Details',
            'recorded_by' => 'Entered By',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ExpenseCategories::class, ['category_id' => 'category_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->recorded_by = Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }

    /**
     * =========================================================================
     * FINANCIAL ANALYTICS FUNCTIONS
     * These help answer business questions directly from code
     * =========================================================================
     */

    /**
     * Get total expenses for a specific period
     */
    public static function getTotalForPeriod($from, $to)
    {
        return (float) self::find()
            ->where(['between', 'expense_date', $from, $to])
            ->andWhere(['is_deleted' => 0])
            ->sum('amount');
    }

    /**
     * Get total income (payments) for a specific period (from existing BillingPayments)
     */
    public static function getTotalIncomeForPeriod($from, $to)
    {
        return (float) BillingPayments::find()
            ->where(['between', 'transaction_date', $from, $to])
            ->sum('amount');
    }

    /**
     * Get a full P&L summary for a period
     * Returns: ['income' => X, 'expenses' => Y, 'profit' => Z]
     */
    public static function getProfitLossSummary($from, $to)
    {
        $income = self::getTotalIncomeForPeriod($from, $to);
        $expenses = self::getTotalForPeriod($from, $to);
        
        return [
            'income' => $income,
            'expenses' => $expenses,
            'profit' => $income - $expenses
        ];
    }

    /**
     * Get expenses grouped by category for a period (useful for charts)
     */
    public static function getBreakdownByCategory($from, $to)
    {
        return self::find()
            ->joinWith('category')
            ->select(['category_name', 'SUM(amount) as total_amount'])
            ->where(['between', 'expense_date', $from, $to])
            ->andWhere(['expenses.is_deleted' => 0])
            ->groupBy('expenses.category_id')
            ->asArray()
            ->all();
    }
}
