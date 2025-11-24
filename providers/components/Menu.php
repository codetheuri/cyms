<?php

namespace helpers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\Menu as BaseMenu;

class Menu extends BaseMenu
{
    public $itemOptions = [ 'class'=>"nav-main-item" ];
    public $linkTemplate = '<a  class="nav-main-link {active}" href="{url}">{label}</a>';
    public $labelTemplate = '{label}';
    public $submenuTemplate = "\n<ul class=\"nav-main-submenu\">\n{items}\n</ul>\n";
    public $encodeLabels = false;
    public $activeCssClass = 'active';
    public $activateItems = true;
    public $activateParents = true;
    public $hideEmptyItems = true;
    public $options = ['class' => 'nav-main'];

    public static function load($items = [], $subs = [])
    {
        foreach (require dirname(dirname(__DIR__)) . '/config/menus.php' as  $menuKey => $item) {
            $label = '<span class="nav-main-link-name">' . $item['title'] . '</span>';
            $icon = '<i class="nav-main-link-icon fa fa-' . $item['icon'] . '"> </i>';
            if (isset($item['url'])) {
                $items[] = ['label' => $icon . $label, 'url' => [/* '/' . $module_id . */ $item['url']], 'visible' => 1];
            } else {
                foreach ($item['submenus'] as $key => $miniItem) {
                    $url = isset($miniItem['param']) ? [/* '/' . $module_id . */ $miniItem['url'], key($miniItem['param']) => $miniItem['param'][key($miniItem['param'])]] : [/* '/' . $module_id . */ $miniItem['url']];
                    $subs[$menuKey][] = ['label' => $miniItem['title'], 'url' => $url, 'visible' => 1];
                }
                if (!empty($subs[$menuKey])) {
                    $items[] = [
                        'label' => $icon .  $label,
                        'url' => '#',
                        'template' => ' <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">{label}</a>',
                        'items' => $subs[$menuKey],
                        //'options' => ['class' => "nav-main-item"],
                        'visible' => 1,
                    ];
                }
            }
        }

        return self::widget(['items' => $items]);
    }
    protected function renderItem($item)
    {
        if (isset($item['url'])) {
            $template = ArrayHelper::getValue($item, 'template', $this->linkTemplate);

            return strtr($template, [
                '{url}' => Html::encode(Url::to($item['url'])),
                '{label}' => $item['label'],
                '{active}' => $item['active'] ? $this->activeCssClass : null,
            ]);
        }

        $template = ArrayHelper::getValue($item, 'template', $this->labelTemplate);

        return strtr($template, [
            '{label}' => $item['label'],
        ]);
    }

    protected function renderItems($items)
    {
        $n = count($items);
        $lines = [];
        foreach ($items as $i => $item) {
            $options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
            $tag = ArrayHelper::remove($options, 'tag', 'li');
            $class = [];
            if ($item['active']) {
                $class[] =  (empty($item['items'])) ? ''/* $this->activeCssClass */ :  'open';
            }
            if ($i === 0 && $this->firstItemCssClass !== null) {
                $class[] = $this->firstItemCssClass;
            }
            if ($i === $n - 1 && $this->lastItemCssClass !== null) {
                $class[] = $this->lastItemCssClass;
            }
            Html::addCssClass($options, $class);

            $menu = $this->renderItem($item);
            if (!empty($item['items'])) {
                $submenuTemplate = ArrayHelper::getValue($item, 'submenuTemplate', $this->submenuTemplate);
                $menu .= strtr($submenuTemplate, [
                    '{items}' => $this->renderItems($item['items']),
                ]);
            }
            $lines[] = Html::tag($tag, $menu, $options);
        }

        return implode("\n", $lines);
    }
}
