<?php

namespace dashboard\models;

use Yii;
use yii\behaviors\TimestampBehavior;

class ClientRates extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%client_rates}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public function rules()
    {
        return [
            [['owner_id', 'container_type_id', 'daily_rate'], 'required'],
            [['owner_id', 'container_type_id'], 'integer'],
            [['daily_rate'], 'number', 'min' => 0],
            [['container_type_id'], 'unique', 'targetAttribute' => ['owner_id', 'container_type_id'], 'message' => 'Rate for this container type already exists for this client.'],
        ];
    }

    public function getContainerType()
    {
        return $this->hasOne(MasterContainerTypes::class, ['type_id' => 'container_type_id']);
    }
}