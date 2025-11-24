<?php

use yii\rbac\Item;
use yii\db\Migration;
use auth\hooks\Configs;
use auth\models\AuthItem;
use helpers\traits\Keygen;
use auth\models\Assignment;

/**
 * Class m240118_110956_default_user_presets
 */
class m240118_110956_default_user_presets extends Migration
{
    use Keygen;
    public function up()
    {
        $tableOptions = null;
        $auth = Configs::authManager();
        //default roles
        foreach (['su' => 'Super User', 'editor' => 'Editor', 'viewer' => 'Viewer', 'api' => 'API User'] as $key => $value) {
            $model = new AuthItem(null);
            $model->type = Item::TYPE_ROLE;
            $model->name = $key;
            $model->data = $value;
            $model->save(false);
        }
        (new AuthItem($auth->getRole('su')))->addChildren(['editor', 'viewer']);
        //default permissions
        foreach ((new AuthItem(null))->scanPermissions() as $key => $value) {
            $model = new AuthItem(null);
            $model->type = Item::TYPE_PERMISSION;
            $model->name = $key;
            $model->data = $value;
            if ($model->save(false)) {
                $str = str_replace('-', ' ', $model->name);
                if (!str_contains($model->name, '-')) {
                    (new AuthItem($auth->getRole('api')))->addChildren([$model->name]);
                } else {
                    if (str_contains($str, 'create') || str_contains($str, 'update')) {
                        (new AuthItem($auth->getRole('editor')))->addChildren([$model->name]);
                    } elseif (str_contains($str, 'list')) {
                        (new AuthItem($auth->getRole('viewer')))->addChildren([$model->name]);
                    } else {
                        (new AuthItem($auth->getRole('su')))->addChildren([$model->name]);
                    }
                }
            }
        }

        $this->createTable('{{%profiles}}', [
            'profile_id' => $this->bigPrimaryKey(),
            'user_id' => $this->bigInteger(),
            'first_name' => $this->string(50)->notNull(),
            'middle_name' => $this->string(50),
            'last_name' => $this->string(50)->notNull(),
            'email_address' => $this->string(128)->notNull(),
            'mobile_number' => $this->string(15),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'FOREIGN KEY ([[user_id]]) REFERENCES {{%users}} ([[user_id]])' .
                $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        $uid = $this->uid('USERS', true);
        $this->insert('{{%users}}', array(
            'user_id' => $uid,
            'username' => 'admin',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            'created_at' => time(),
            'updated_at' => time(),
        ));
        $this->insert('{{%profiles}}', array(
            'user_id' => $uid,
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email_address' => 'admin@' . strtolower($_ENV['APP_CODE']) . '.com',
            'created_at' => time(),
            'updated_at' => time(),
        ));
        (new Assignment($uid))->assign(['su']);
        $uid = $this->uid('USERS', true);
        $this->insert('{{%users}}', array(
            'user_id' => $uid,
            'username' => 'user',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash('user'),
            'created_at' => time(),
            'updated_at' => time(),
        ));
        $this->insert('{{%profiles}}', array(
            'user_id' => $uid,
            'first_name' => 'Standard',
            'last_name' => 'User',
            'email_address' => 'user@' . strtolower($_ENV['APP_CODE']) . '.com',
            'created_at' => time(),
            'updated_at' => time(),
        ));
        (new Assignment($uid))->assign(['su']);
    }

    public function down()
    {
        $this->delete((Yii::$app->getAuthManager())->assignmentTable);
        $this->delete((Yii::$app->getAuthManager())->itemChildTable);
        $this->delete((Yii::$app->getAuthManager())->itemTable);
        $this->delete((Yii::$app->getAuthManager())->ruleTable);
        $this->dropTable('{{%profiles}}');
    }
    protected function buildFkClause($delete = '', $update = '')
    {
        return implode(' ', ['', $delete, $update]);
    }
}
