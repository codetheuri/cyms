<?php

namespace dashboard\models;

use Yii;

/**
 * This is the model class for table "expense_categories".
 *
 * @property int $category_id
 * @property string $category_name
 * @property string|null $description
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Expenses[] $expenses
 */
class ExpenseCategories extends BaseModel
{
    public static function tableName()
    {
        return '{{%expense_categories}}';
    }

    public function rules()
    {
        return [
            [['category_name'], 'required'],
            [['description'], 'string'],
            [['status', 'is_deleted', 'created_at', 'updated_at'], 'integer'],
            [['category_name'], 'string', 'max' => 100],
            [['category_name'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'category_id' => 'Category ID',
            'category_name' => 'Category Name',
            'description' => 'Description',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses()
    {
        return $this->hasMany(Expenses::class, ['category_id' => 'category_id']);
    }

    /**
     * Helper to get list of active categories for dropdowns
     */
    public static function getActiveList()
    {
        return \yii\helpers\ArrayHelper::map(
            self::find()->where(['status' => 10])->all(),
            'category_id',
            'category_name'
        );
    }
}
