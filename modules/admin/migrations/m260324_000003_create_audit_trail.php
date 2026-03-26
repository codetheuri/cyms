<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%audit_trail}}`.
 */
class m260324_000003_create_audit_trail extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%audit_trail}}', [
            'id' => $this->bigPrimaryKey(),
            'audit_time' => $this->integer()->notNull(),
            'model_name' => $this->string(100)->notNull(),
            'operation' => $this->string(64)->notNull(),
            'request_method' => $this->string(16)->notNull(),
            'field_name' => $this->string(255)->notNull(),
            'old_value' => $this->text(),
            'new_value' => $this->text(),
            'user_id' => $this->string(64)->notNull(),
            'duration' => $this->double()->notNull(),
            'memory_max' => $this->integer()->notNull(),
            'request_route' => $this->string()->notNull(),
            'headers' => $this->text(),
            'query_params' => $this->text(),
            'body_params' => $this->text(),
            'raw_body' => $this->text(),
            'url' => $this->text()->notNull(),
            'ip_address' => $this->string(64)->notNull(),
            'user_agent' => $this->text(),
            'is_deleted' => $this->integer(2)->notNull()->defaultValue(0),
            'status' => $this->integer(3)->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-audit-model', '{{%audit_trail}}', 'model_name');
        $this->createIndex('idx-audit-user', '{{%audit_trail}}', 'user_id');
        $this->createIndex('idx-audit-time', '{{%audit_trail}}', 'audit_time');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%audit_trail}}');
    }
}
