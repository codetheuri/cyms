<?php

namespace helpers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\Menu as BaseMenu;

class SettingMenu extends BaseMenu
{
    public $options = [];
    public $itemOptions = ['class' => 'nav-item'];
    public $linkTemplate = '<a href="{url}" class="nav-link {active}">{icon} <span>{label}</span></a>';
    public $activeCssClass = 'active';
    public $encodeLabels = false;
    public $activateItems = true;
    public $activateParents = true;

    public static function load($items = [])
    {
        $menuConfig = require Yii::getAlias('@app/config/settings_menu.php');

        // Get current user roles (as array of role names)
        $userRoles = [];
        if (!Yii::$app->user->isGuest) {
            $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
            $userRoles = array_keys($roles);
        }

        foreach ($menuConfig as $item) {
            // If the menu has a roles restriction, check if the user has at least one matching role
            if (!empty($item['roles'])) {
                // Skip if no intersection between userRoles and item['roles']
                if (empty(array_intersect($userRoles, $item['roles']))) {
                    continue;
                }
            }

            $items[] = [
                'label' => $item['title'],
                'url' => [$item['url']],
                'icon' => '<i class="' . Html::encode($item['icon']) . '"></i>',
                'active' => Yii::$app->controller->id === $item['activeOn'],
            ];
        }

        return Html::tag(
            'div',
            parent::widget(['items' => $items]),
            ['class' => 'widget settings-menu mb-0']
        );
    }

    protected function renderItem($item)
    {
        $template = ArrayHelper::getValue($item, 'template', $this->linkTemplate);

        return strtr($template, [
            '{url}' => Html::encode(Url::to($item['url'])),
            '{label}' => $item['label'],
            '{icon}' => isset($item['icon']) ? $item['icon'] : '',
            '{active}' => !empty($item['active']) ? $this->activeCssClass : '',
        ]);
    }
}
