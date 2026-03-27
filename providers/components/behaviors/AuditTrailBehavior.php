<?php

namespace helpers\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use helpers\models\AuditTrail;

/**
 * Class AuditTrailBehavior
 * Automatically tracks all changes (INSERT, UPDATE) and logs them into audit_trail table.
 *
 * @package helpers\behaviors
 */
class AuditTrailBehavior extends Behavior
{
    /**
     * @var string Label for when no user is logged in
     */
    const NO_USER_ID = "NO_USER";

    /**
     * List of models to ignore for auditing
     * @var array
     */
    public $ignoredModels = [
        AuditTrail::class,
    ];

    /**
     * List of attributes to ignore for auditing
     * @var array
     */
    public $ignoredAttributes = ['updated_at', 'created_at', 'password_hash', 'auth_key', 'password_reset_token'];

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        if (Yii::$app instanceof \yii\console\Application) {
            return [];
        }

        $ownerClass = get_class($this->owner);
        if (in_array($ownerClass, $this->ignoredModels, true)) {
            return [];
        }

        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    /**
     * Track and log changes
     *
     * @param $event
     * @return bool
     */
    public function afterSave($event)
    {
        $app = Yii::$app;
        if (!$app->has('request')) return true; // Handle non-web requests if any
        $request = $app->request;
        
        $userId = self::NO_USER_ID;
        if (!$app->user->isGuest) {
            $userId = $app->user->identity->user_id ?? $app->user->id;
        }

        $newAttributes = $this->owner->getAttributes();
        $oldAttributes = $event->changedAttributes;
        
        $moduleName = isset($app->controller->module) ? $app->controller->module->id : 'app';
        $modelName = $moduleName . ': ' . $this->getShortClassName($this->owner);

        // Pre-calculate common metadata
        $metadata = [
            'audit_time' => time(),
            'model_name' => $modelName,
            'user_id' => (string) $userId,
            'ip_address' => AuditTrail::getClientIp(),
            'request_method' => $request->method,
            'duration' => (float) (microtime(true) - YII_BEGIN_TIME),
            'memory_max' => (int) memory_get_peak_usage(),
            'request_route' => $app->requestedAction ? $app->requestedAction->uniqueId : null,
            'user_agent' => $request->userAgent,
            'headers' => json_encode($request->headers->toArray(), JSON_UNESCAPED_SLASHES),
            'query_params' => json_encode($request->queryParams, JSON_UNESCAPED_SLASHES),
            'body_params' => json_encode($this->filterBodyParams($request->bodyParams), JSON_UNESCAPED_SLASHES),
            'raw_body' => $request->getRawBody(),
            'url' => Url::base(true) . $request->url,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $oldValues = [];
        $newValues = [];
        $operation = 'UPDATE';
        $fieldName = 'Multiple';

        if ($this->owner->isNewRecord || $event->name === ActiveRecord::EVENT_AFTER_INSERT) {
            $operation = 'INSERT';
            $fieldName = 'All Fields';
            foreach ($newAttributes as $name => $value) {
                if (in_array($name, $this->ignoredAttributes)) continue;
                if ($value === null || $value === '') continue; // Skip empty fields on insert to save space
                $newValues[$name] = $value;
                $oldValues[$name] = null;
            }
        } else {
            foreach ($oldAttributes as $name => $oldValue) {
                if (in_array($name, $this->ignoredAttributes)) continue;
                
                $newValue = $newAttributes[$name] ?? null;
                
                if ($oldValue != $newValue) {
                    $oldValues[$name] = $oldValue;
                    $newValues[$name] = $newValue;
                    
                    if ($name === 'is_deleted') {
                        $operation = $newValue ? 'DELETE' : 'RESTORE';
                    }
                }
            }
            
            if (count($newValues) === 1) {
                $fieldName = array_key_first($newValues);
            } elseif (empty($newValues)) {
                return true; // No actual changes tracked
            }
        }

        $log = new AuditTrail();
        $log->setAttributes($metadata);
        $log->operation = $operation;
        $log->field_name = $fieldName;
        $log->old_value = json_encode($oldValues, JSON_UNESCAPED_SLASHES);
        $log->new_value = json_encode($newValues, JSON_UNESCAPED_SLASHES);
        $log->save(false);

        return true;
    }

    protected function getShortClassName($object)
    {
        $className = get_class($object);
        return substr($className, strrpos($className, '\\') + 1);
    }

    protected function filterBodyParams($params)
    {
        if (!is_array($params)) return $params;
        $sensitive = ['password', 'confirm_password', 'auth_key', 'token'];
        foreach ($params as $key => &$value) {
            if (is_array($value)) $value = $this->filterBodyParams($value);
            elseif (in_array(strtolower($key), $sensitive)) $value = '********';
        }
        return $params;
    }
}
