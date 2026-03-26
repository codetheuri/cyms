<?php

namespace helpers\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * AuditTrail model
 *
 * @property int $id
 * @property int $audit_time
 * @property string $model_name
 * @property string $operation
 * @property string $request_method
 * @property string $field_name
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string $user_id
 * @property float $duration
 * @property int $memory_max
 * @property string $request_route
 * @property string|null $headers
 * @property string|null $query_params
 * @property string|null $body_params
 * @property string|null $raw_body
 * @property string $url
 * @property string $ip_address
 * @property string|null $user_agent
 * @property int $is_deleted
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class AuditTrail extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%audit_trail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['audit_time', 'model_name', 'operation', 'request_method', 'field_name', 'user_id', 'duration', 'memory_max', 'request_route', 'url', 'ip_address', 'created_at', 'updated_at'], 'required'],
            [['audit_time', 'memory_max', 'is_deleted', 'status', 'created_at', 'updated_at'], 'integer'],
            [['old_value', 'new_value', 'headers', 'query_params', 'body_params', 'raw_body', 'url', 'user_agent'], 'string'],
            [['duration'], 'number'],
            [['model_name'], 'string', 'max' => 100],
            [['operation', 'field_name'], 'string', 'max' => 32],
            [['request_method'], 'string', 'max' => 16],
            [['user_id', 'ip_address'], 'string', 'max' => 20],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        $identityClass = Yii::$app->user->identityClass ?? 'auth\models\User';
        return $this->hasOne($identityClass, ['user_id' => 'user_id']);
    }

    /**
     * Map technical operations to user-friendly business terms
     */
    public function getFriendlyOperation()
    {
        $cleanModel = trim(str_replace(['dashboard:', 'admin:'], '', $this->model_name));
        
        if ($cleanModel === 'ContainerVisits') {
            if ($this->operation === 'INSERT') return 'Gate In';
            if ($this->operation === 'UPDATE' && strpos($this->new_value, 'time_out') !== false) return 'Gate Out';
            if ($this->operation === 'DELETE') return 'Gate In Revocation';
        }
        
        if ($cleanModel === 'ContainerSurveys') return 'Container Survey';
        if ($cleanModel === 'BillingRecords') return 'Invoicing / Billing';
        if ($cleanModel === 'BillingPayments') return 'Payment Collection';
        if ($cleanModel === 'User Session') return $this->operation; // Already friendly (LOGIN/LOGOUT)
        
        $labels = [
            'INSERT' => 'Created Record',
            'UPDATE' => 'Modified Data',
            'DELETE' => 'Deleted Record',
            'RESTORE' => 'Restored Record',
            'PRINT' => 'Report Printed',
            'CHANGE_PASSWORD' => 'Password Change',
            'RESET_PASSWORD' => 'Account Reset',
        ];
        
        return $labels[$this->operation] ?? $this->operation;
    }

    /**
     * Manual logging helper for actions that don't involve ActiveRecord saves (e.g. printing reports)
     * 
     * @param string $operation e.g. 'PRINT', 'ACCESS', 'SEARCH'
     * @param string $entity e.g. 'Inward Report'
     * @param string|null $fieldName e.g. 'All Records'
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param string|null $userId Specific User ID to log against
     */
    public static function logAction($operation, $entity, $fieldName = 'N/A', $oldValue = null, $newValue = null, $userId = null)
    {
        $app = Yii::$app;
        if ($app instanceof \yii\console\Application) return;

        $request = $app->request;
        
        if ($userId === null) {
            $userId = !$app->user->isGuest ? ($app->user->identity->user_id ?? $app->user->id) : 'NO_USER';
        }

        $log = new self();
        $log->audit_time = time();
        $log->model_name = $entity;
        $log->operation = $operation;
        $log->request_method = $request->method;
        $log->field_name = $fieldName;
        $log->old_value = (string)$oldValue;
        $log->new_value = (string)$newValue;
        $log->user_id = (string)$userId;
        $log->duration = (float)(microtime(true) - YII_BEGIN_TIME);
        $log->memory_max = (int)memory_get_peak_usage();
        $log->request_route = $app->requestedAction ? $app->requestedAction->uniqueId : 'N/A';
        $log->url = \yii\helpers\Url::base(true) . $request->url;
        $log->ip_address = $request->getUserIP() ?? '0.0.0.0';
        $log->user_agent = $request->userAgent;
        $log->headers = json_encode($request->headers->toArray(), JSON_UNESCAPED_SLASHES);
        $log->query_params = json_encode($request->queryParams, JSON_UNESCAPED_SLASHES);
        $log->body_params = json_encode($request->bodyParams, JSON_UNESCAPED_SLASHES);
        $log->created_at = time();
        $log->updated_at = time();
        $log->save(false);
    }
}
