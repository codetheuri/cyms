<?php

use yii\db\Migration;

/**
 * Handles the creation of tables `expense_categories` and `expenses`.
 */
class m260331_000001_create_expenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // 1. Create Expense Categories Table
        $this->createTable('{{%expense_categories}}', [
            'category_id' => $this->primaryKey(),
            'category_name' => $this->string(100)->notNull()->unique(),
            'description' => $this->text(),
            'status' => $this->integer(2)->notNull()->defaultValue(10), // 10: Active, 9: Inactive, 1: Deleted
            
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // 2. Create Expenses Table
        $this->createTable('{{%expenses}}', [
            'expense_id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(15, 2)->notNull(),
            'expense_date' => $this->date()->notNull(),
            'reference_no' => $this->string(100),
            'payment_method' => $this->string(50)->notNull()->defaultValue('CASH'),
            'description' => $this->text(),
            'recorded_by' => $this->integer(),
            'is_deleted' => $this->integer(2)->notNull()->defaultValue(0),
            'status' => $this->integer(2)->notNull()->defaultValue(10), 
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // 3. Add Foreign Key for category
        $this->addForeignKey(
            'fk-expense-category',
            '{{%expenses}}',
            'category_id',
            '{{%expense_categories}}',
            'category_id',
            'CASCADE'
        );

        // 4. Indexing for performance
        $this->createIndex('idx-expense-date', '{{%expenses}}', 'expense_date');
        $this->createIndex('idx-expense-category', '{{%expenses}}', 'category_id');

        // 5. Seed some initial common categories
        $commonCategories = ['Fuel', 'Staff Salaries', 'Electricity', 'Water', 'Maintenance', 'Rent', 'Security', 'Taxes', 'Other'];
        foreach ($commonCategories as $cat) {
            $this->insert('{{%expense_categories}}', [
                'category_name' => $cat,
                'description' => 'System default category',
                'status' => 10,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-expense-category', '{{%expenses}}');
        $this->dropTable('{{%expenses}}');
        $this->dropTable('{{%expense_categories}}');
    }
}
