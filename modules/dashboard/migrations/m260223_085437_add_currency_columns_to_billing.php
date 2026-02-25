<?php

use yii\db\Migration;

class m260223_085437_add_currency_columns_to_billing extends Migration
{
    
  public function safeUp()
    {
        // 1. Add preferred currency to the Owner/Client table
        $this->addColumn('{{%master_container_owners}}', 'billing_currency', $this->string(3)->defaultValue('KES')->after('owner_contact'));

        // 2. Add dual-currency tracking to Billing Records
        $this->addColumn('{{%billing_records}}', 'currency', $this->string(3)->defaultValue('KES')->after('balance'));
        $this->addColumn('{{%billing_records}}', 'exchange_rate', $this->decimal(10, 4)->defaultValue(1.0000)->after('currency'));
        $this->addColumn('{{%billing_records}}', 'foreign_grand_total', $this->decimal(12, 2)->defaultValue(0.00)->after('exchange_rate'));
        $this->addColumn('{{%billing_records}}', 'foreign_balance', $this->decimal(12, 2)->defaultValue(0.00)->after('foreign_grand_total'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%master_container_owners}}', 'billing_currency');
        $this->dropColumn('{{%billing_records}}', 'currency');
        $this->dropColumn('{{%billing_records}}', 'exchange_rate');
        $this->dropColumn('{{%billing_records}}', 'foreign_grand_total');
        $this->dropColumn('{{%billing_records}}', 'foreign_balance');
    }
}
