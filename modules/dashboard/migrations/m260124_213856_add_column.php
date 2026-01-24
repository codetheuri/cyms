<?php

use yii\db\Migration;

class m260124_213856_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {


    $this->addColumn('{{%billing_records}}', 'atl_number', $this->string(50)->after('authorized_by'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%billing_records}}', 'atl_number');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260124_213856_add_column cannot be reverted.\n";

        return false;
    }
    */
}
