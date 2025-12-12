<?php
// helpers/PermissionHelper.php
namespace helpers;

use Yii;

class PermissionHelper
{
    /**
     * Check permission without throwing exceptions
     */
    public static function can($permission)
    {
        try {
            return Yii::$app->user->can($permission);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check multiple permissions (OR logic)
     */
    public static function canAny($permissions)
    {
        foreach ($permissions as $permission) {
            if (self::can($permission)) {
                return true;
            }
        }
        return false;
    }
}