<?php

use yii\db\Migration;

class m260331_085422_add_is_deleted_column_expenses_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('{{%expense_categories}}', 'is_deleted', $this->integer(2)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
       $this->dropColumn('{{%expense_categories}}', 'is_deleted');
    }

  
}
