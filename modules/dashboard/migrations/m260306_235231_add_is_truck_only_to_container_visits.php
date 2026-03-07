<?php

use yii\db\Migration;

/**
 * Class m260306_235231_add_is_truck_only_to_container_visits
 */
class m260306_235231_add_is_truck_only_to_container_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add `is_truck_only` column
        $this->addColumn('{{%container_visits}}', 'is_truck_only', $this->boolean()->notNull()->defaultValue(0));
        
        // Change `container_number` to allow null
        $this->alterColumn('{{%container_visits}}', 'container_number', $this->string(20)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%container_visits}}', 'container_number', $this->string(20)->notNull());
        $this->dropColumn('{{%container_visits}}', 'is_truck_only');
    }
}
