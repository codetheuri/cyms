<?php

use yii\db\Migration;

class m260312_202257_add_comments_out_to_container_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%container_visits}}', 'comments_out', $this->text()->after('comments_in'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%container_visits}}', 'comments_out');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260312_202257_add_comments_out_to_container_visits cannot be reverted.\n";

        return false;
    }
    */
}
