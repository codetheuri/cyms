<?php
namespace dashboard\models;

/**
 * This is the base model class for dashboard module.
 */
class BaseModel extends \helpers\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'audit' => [
                'class' => \helpers\behaviors\AuditTrailBehavior::class,
            ],
        ]);
    }
}
