<?php

use yii\db\Migration;

class m260218_182730_add_client_charges extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%client_rates}}', [
            'rate_id' => $this->primaryKey(),
            'owner_id' => $this->bigInteger()->notNull(),
            'container_type_id' => $this->bigInteger()->notNull(),
            'daily_rate' => $this->decimal(10, 2)->notNull()->defaultValue(0),
               'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[owner_id]]) REFERENCES {{%master_container_owners}} ([[owner_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
            'FOREIGN KEY ([[container_type_id]]) REFERENCES {{%master_container_types}} ([[type_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%client_rates}}');
    }

    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }
}
