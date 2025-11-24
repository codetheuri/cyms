<?php

namespace dashboard\controllers;

use Yii;
use yii\rbac\Item;
use auth\hooks\Configs;
use auth\models\AuthItem;
use auth\hooks\ItemController;

class PermissionController extends ItemController
{
    public $permissions = [
        'list-rbac-items' => 'View Permissions and Roles',
        'create-rbac-items' => 'Add Permission and Role',
        'update-rbac-items' => 'Edit Permission and Role',
        'delete-rbac-items' => 'Delete Permission and Role',
        'manage-roles' => 'Manage Access Roles',
        'sync-permissions' => 'Synchronize New Permissions',
    ];
    public function actionSync()
    {
        Yii::$app->user->can('sync-permissions');
        $success = $failed = 0;
        $auth = Configs::authManager();
        foreach ((new AuthItem(null))->scanPermissions() as $key => $value) {
            $model = new AuthItem(null);
            $model->type = $this->type;
            $model->name = $key;
            $model->data = $value;
            if ($model->save(false)) {
                $success++;
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
            } else {
                $failed++;
            }
        }
        Yii::$app->session->setFlash('info', $success . ' permissions synchronized, ' . $failed . ' permissions failed');
        return $this->redirect(['index']);
    }
    /**
     * @inheritdoc
     */
    public function labels()
    {
        return [
            'Item' => 'Permission',
            'Items' => 'Permissions',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return Item::TYPE_PERMISSION;
    }
}
