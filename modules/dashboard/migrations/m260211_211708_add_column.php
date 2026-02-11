<?php

use yii\db\Migration;

class m260211_211708_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%container_visits}}', 'party_delivering_container', $this->string(100)->after('truck_owner_contact_in'));
        $this->addColumn('{{%container_surveys}}', 'remarks', $this->text()->after('survey_photo_path'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('{{%container_surveys}}', 'remarks');
        $this->dropColumn('{{%container_visits}}', 'party_delivering_container');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260211_211708_add_column cannot be reverted.\n";

        return false;
    }
    */
}
