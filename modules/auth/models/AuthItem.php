<?php

namespace auth\models;

use auth\hooks\Configs;
use auth\hooks\Helper;
use auth\controllers\AssignmentController;
use auth\Module;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\rbac\Item;
use yii\rbac\Rule;

/**
 * This is the model class for table "tbl_auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $ruleName
 * @property string $data
 *
 * @property Item $item
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AuthItem extends Model
{
    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;

    /**
     * @var Item
     */
    private $_item;

    /**
     * Initialize object
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item = null, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data;
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ruleName'], 'checkRule'],
            [['name', 'type', 'data'], 'required'],
            [['name'], 'checkUnique', 'when' => function () {
                return $this->isNewRecord || ($this->_item->name != $this->name);
            }],
            [['type'], 'integer'],
            [['description', 'ruleName'], 'default'],
            [['name'], 'string', 'max' => 64],
            ['name', 'match', 'pattern' => '/^[a-z0-9\-]*$/', 'message' => 'Code should consist of lowercase letters (a-z), numbers or hyphens'],
            ['data', 'match', 'pattern' => '/^[a-zA-Z\s]*$/', 'message' => 'Only Aphabets and spaces are allowed'],
            ['data', 'trim'],
            ['name', 'trim']
        ];
    }

    /**
     * Check role is unique
     */
    public function checkUnique()
    {
        $authManager = Configs::authManager();
        $value = $this->name;
        if ($authManager->getRole($value) !== null || $authManager->getPermission($value) !== null) {
            $message = Yii::t('yii', '{attribute} "{value}" has already been taken.');
            $params = [
                'attribute' => $this->getAttributeLabel('name'),
                'value' => $value,
            ];
            $this->addError('name', Yii::$app->getI18n()->format($message, $params, Yii::$app->language));
        }
    }
 
    /**
     * Check for rule
     */
    public function checkRule()
    {
        $name = $this->ruleName;
        if (!Configs::authManager()->getRule($name)) {
            try {
                $rule = Yii::createObject($name);
                if ($rule instanceof Rule) {
                    $rule->name = $name;
                    Configs::authManager()->add($rule);
                } else {
                    $this->addError('ruleName',  'Invalid rule "{value}"', ['value' => $name]);
                }
            } catch (\Exception $exc) {
                $this->addError('ruleName',  'Rule "{value}" does not exists', ['value' => $name]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' =>  'Code',
            'title' =>  'Name',
            'type' =>  'Type',
            'description' =>  'Description',
            'ruleName' =>  'Rule Name',
            'data' =>  'Data',
        ];
    }

    /**
     * Check if is new record.
     * @return boolean
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * Find role
     * @param string $id
     * @return null|\self
     */
    public static function find($id)
    {
        $item = Configs::authManager()->getRole($id);
        if ($item !== null) {
            return new self($item);
        }

        return null;
    }

    /**
     * Save role to [[\yii\rbac\authManager]]
     * @return boolean
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Configs::authManager();
            if ($this->_item === null) {
                if ($this->type == Item::TYPE_ROLE) {
                    $this->_item = $manager->createRole($this->name);
                } else {
                    $this->_item = $manager->createPermission($this->name);
                }
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = $this->data;
            if ($isNew) {
                $manager->add($this->_item);
            } else {
                $manager->update($oldName, $this->_item);
            }
            Helper::invalidate();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function addChildren($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        if ($this->_item) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->addChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }
        return $success;
    }
    public function removeChildren($items)
    {
        $manager = Configs::authManager();
        $success = 0;
        if ($this->_item !== null) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($this->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->removeChild($this->_item, $child);
                    $success++;
                } catch (\Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            Helper::invalidate();
        }
        return $success;
    }
    public function getItems()
    {
        $manager = Configs::authManager();
        $available = [];
        if ($this->type == Item::TYPE_ROLE) {
            foreach ($manager->getRoles() as $code => $item) {
                $available[$code] = ['type' => 'role', 'display_name' => $item->data];
            }
        }
        foreach (array_keys($manager->getPermissions()) as $name) {
            $available[$name] = ['type' => 'permission', 'display_name' => ($manager->getPermission($name))->data];
        }

        $assigned = [];
        foreach ($manager->getChildren($this->_item->name) as $item) {
            $assigned[$item->name] = $item->type == 1 ? ['type' => 'role', 'display_name' => $item->data] :
                ['type' => 'permission', 'display_name' => $item->data];
            unset($available[$item->name]);
        }
        unset($available[$this->name]);
        ksort($available);
        ksort($assigned);
        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }
    public function getItem()
    {
        return $this->_item;
    }
    public function scanPermissions($list = [])
    {
        $manager = Configs::authManager();
        $exist =  ArrayHelper::map($manager->getPermissions(), 'name', 'data');
        foreach (new \DirectoryIterator(Yii::getAlias('@modules')) as $index => $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $dir = Yii::getAlias('@modules/') . $fileinfo->getFilename() . '/controllers';
                foreach (glob("{$dir}/*.php") as $filename) {
                    $controller  = str_replace('.php', '', str_replace('/', '\\', str_replace(Yii::getAlias('@modules'), "", $filename)));
                    $controller = new $controller(Yii::$app->id, true);
                    if (property_exists($controller, 'permissions')) {
                        $list = array_merge($list, $controller->permissions);
                    }
                }
            }
        }
        return array_diff_key($list,$exist);
    }
    public static function getTypeName($type = null)
    {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
            Item::TYPE_ROLE => 'Role',
        ];
        if ($type === null) {
            return $result;
        }

        return $result[$type];
    }
}
