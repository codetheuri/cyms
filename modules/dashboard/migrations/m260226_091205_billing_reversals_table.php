<?php

use yii\db\Migration;

class m260226_091205_billing_reversals_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%billing_reversals}}', [
            'id' => $this->bigPrimaryKey(),
            'bill_id' => $this->bigInteger()->notNull(),
            'old_invoice_number' => $this->string(50)->notNull(),
            'old_grand_total' => $this->decimal(10, 2)->notNull(), 
            'old_currency' => $this->string(3)->notNull(),
            'reversal_reason' => $this->text()->notNull(),
            'reversed_by' => $this->integer()->notNull(),
            'snapshot_data' => $this->text()->notNull(),
            'is_deleted' => $this->boolean()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),

            'FOREIGN KEY ([[bill_id]]) REFERENCES {{%billing_records}} ([[bill_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%billing_reversals}}');
    }

    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }
}
