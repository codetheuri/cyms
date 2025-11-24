<?php

use yii\db\Migration;

/**
 * Class m231209_121135_init
 */
class m231209_121135_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        $this->createTable('{{%incrementer}}', [
            'id' => $this->primaryKey(),
            'year' => $this->integer(5)->notNull(),
            'value' => $this->integer()->notNull(),
            'type' => $this->string(50)->notNull(),
            'is_deleted' => $this->integer(2)->notNull()->defaultValue(0),
            'status' => $this->integer(3)->notNull()->defaultValue(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%users}}', [
            'user_id' => $this->bigInteger(),
            'username' => $this->string(64)->notNull()->unique(),
            'auth_key' => $this->string(32),
            'password_hash' => $this->string()->notNull(),
            'status' => $this->integer(4)->notNull()->defaultValue(10),
            
            'is_deleted' => $this->integer(2)->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            
            'PRIMARY KEY ([[user_id]])',
        ], $tableOptions);
        
        $this->createTable('{{%password_history}}', [
            'user_id' => $this->bigInteger()->notNull(),
            'old_password' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        $this->createTable('{{%tokens}}', [
            'token_id' => $this->string(64)->notNull(),
            'user_id' => $this->bigInteger()->notNull(),
            'token' => $this->string(7)->notNull(),
            'token_type' => $this->string(20)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'PRIMARY KEY ([[token_id]])',
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%tokens}}');
        $this->dropTable('{{%password_history}}');
        $this->dropTable('{{%users}}');
        $this->dropTable('{{%incrementer}}');
    }
    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }
}
