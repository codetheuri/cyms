<?php
// helpers/PermissionMenu.php
namespace helpers;

use Yii;

class PermissionMenu
{
    /**
     * Check permission safely
     */
    public static function can($permission)
    {
        try {
            return Yii::$app->user->can($permission);
        } catch (\yii\base\Exception $e) {
            return false;
        }
    }
    
    /**
     * Load menus with permission checking
     */
    public static function load()
    {
        $menuConfig = require dirname(dirname(__DIR__)) . '/config/menus.php';
        $filteredMenus = [];
        
        foreach ($menuConfig as $menuItem) {
            $filteredItem = self::filterMenuItem($menuItem);
            if ($filteredItem !== null) {
                $filteredMenus[] = $filteredItem;
            }
        }
        
        // Convert to the format expected by your Menu class
        return self::generateMenuItems($filteredMenus);
    }
    
    /**
     * Filter a single menu item based on permissions
     */
    private static function filterMenuItem($item)
    {
        // Check if user has permission for this menu item
        if (isset($item['permission']) && !self::can($item['permission'])) {
            return null;
        }
        
        // If it's a parent menu with submenus
        if (isset($item['submenus'])) {
            $filteredSubmenus = [];
            
            foreach ($item['submenus'] as $submenu) {
                if (isset($submenu['permission']) && !self::can($submenu['permission'])) {
                    continue;
                }
                $filteredSubmenus[] = $submenu;
            }
            
            // Only return parent if it has at least one visible submenu
            if (empty($filteredSubmenus)) {
                return null;
            }
            
            $item['submenus'] = $filteredSubmenus;
        }
        
        return $item;
    }
    
    /**
     * Generate menu items in the format expected by Menu::widget()
     */
    private static function generateMenuItems($menus)
    {
        $items = [];
        $subs = [];
        
        foreach ($menus as $menuKey => $item) {
            $label = '<span class="nav-main-link-name">' . $item['title'] . '</span>';
            $icon = '<i class="nav-main-link-icon fa fa-' . $item['icon'] . '"> </i>';
            
            if (isset($item['url'])) {
                $items[] = [
                    'label' => $icon . $label, 
                    'url' => [$item['url']], 
                    'visible' => true
                ];
            } else {
                foreach ($item['submenus'] as $key => $miniItem) {
                    $url = isset($miniItem['param']) 
                        ? [$miniItem['url'], key($miniItem['param']) => $miniItem['param'][key($miniItem['param'])]] 
                        : [$miniItem['url']];
                    
                    $subs[$menuKey][] = [
                        'label' => $miniItem['title'], 
                        'url' => $url, 
                        'visible' => true
                    ];
                }
                
                if (!empty($subs[$menuKey])) {
                    $items[] = [
                        'label' => $icon . $label,
                        'url' => '#',
                        'template' => ' <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">{label}</a>',
                        'items' => $subs[$menuKey],
                        'visible' => true,
                    ];
                }
            }
        }
        
        return Menu::widget(['items' => $items]);
    }
}