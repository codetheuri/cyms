<?php

use yii\db\Migration;

class m260216_210710_add_credit_flow extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%billing_records}}', 'approval_status', "ENUM('NONE', 'PENDING', 'APPROVED', 'REJECTED') DEFAULT 'NONE' AFTER status");
        $this->addColumn('{{%billing_records}}', 'requester_note', $this->text()->after('approval_status'));
        $this->addColumn('{{%billing_records}}', 'rejection_reason', $this->text()->after('requester_note'));
        $this->addColumn('{{%billing_records}}', 'requested_by', $this->integer()->after('rejection_reason')); // User ID
        $this->addColumn('{{%billing_records}}', 'requested_at', $this->integer()->after('requested_by'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%billing_records}}', 'requested_at');
        $this->dropColumn('{{%billing_records}}', 'requested_by');
        $this->dropColumn('{{%billing_records}}', 'rejection_reason');
        $this->dropColumn('{{%billing_records}}', 'requester_note');
        $this->dropColumn('{{%billing_records}}', 'approval_status');
    }
    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260216_210710_add_credit_flow cannot be reverted.\n";

        return false;
    }
    */
}
