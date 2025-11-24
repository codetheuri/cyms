<?php

namespace auth\models;

use auth\hooks\Configs;
use auth\hooks\Helper;
use Yii;

class Assignment extends \yii\base\BaseObject
{
    /**
     * @var integer User id
     */
    public $id;
    /**
     * @var \yii\web\IdentityInterface User
     */
    public $user;

    /**
     * @inheritdoc
     */
    public function __construct($id, $user = null, $config = array())
    {
        $this->id = $id;
        $this->user = $user;
        parent::__construct($config);
    }

    /**
     * Grands a roles from a user.
     * @param array $items
     * @return integer number of successful grand
     */
    public function assign($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);
                $item = $item ?: $manager->getPermission($name);
                $manager->assign($item, $this->id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Revokes a roles from a user.
     * @param array $items
     * @return integer number of successful revoke
     */
    public function revoke($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        foreach ($items as $name) {
            try {
                $item = $manager->getRole($name);
                $item = $item ?: $manager->getPermission($name);
                $manager->revoke($item, $this->id);
                $success++;
            } catch (\Exception $exc) {
                Yii::error($exc->getMessage(), __METHOD__);
            }
        }
        Helper::invalidate();
        return $success;
    }

    /**
     * Get all available and assigned roles/permission
     * @return array
     */
    public function getItems()
    {
        $manager = Configs::authManager();
        $available = [];
        foreach ($manager->getRoles() as $code => $item) {
            $available[$code] = ['type' => 'role', 'display_name' => $item->data];
        }

        foreach (array_keys($manager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $available[$name] = ['type' => 'permission', 'display_name' => ($manager->getPermission($name))->data];
            }
        }

        $assigned = [];
        foreach ($manager->getAssignments($this->id) as $item) {
            $assigned[$item->roleName] = $available[$item->roleName];
            unset($available[$item->roleName]);
        }

        ksort($available);
        ksort($assigned);
        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($this->user) {
            return $this->user->$name;
        }
    }
}
