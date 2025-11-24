<?php

use yii\db\Migration;

/**
 * Class m240206_054743_settings
 */
class m240206_054743_settings extends Migration
{
    public function up()
    {
        $tableOptions = null;

        $this->createTable('{{%system_settings}}', [
            'key' => $this->string(100)->notNull(),
            'label' => $this->string(100)->notNull(),
            'category' => $this->string(20)->notNull()->defaultValue('GENERAL'),
            'disposition' => $this->integer()->notNull(),
            'input_type' => $this->string(20)->notNull(),
            'current_value' => $this->text(),
            'default_value' => $this->text()->notNull(),
            'input_preload' => $this->text(),
               'is_deleted' => $this->integer(2)->notNull()->defaultValue(0),
              'status' => $this->integer(3)->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
           
            'PRIMARY KEY ([[key]])',
        ], $tableOptions);
        $this->createTable('{{%user_settings}}', [
            'setting_id' => $this->bigPrimaryKey(),
            'user_id' => $this->bigInteger(),
            'configuration_data' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);
    } 
    public function down()
    {
        $this->dropTable('{{%user_settings}}');
        $this->dropTable('{{%sytem_settings}}');
    }
    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }
}
