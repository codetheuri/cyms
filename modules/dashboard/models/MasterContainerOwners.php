<?php
namespace dashboard\models;
use Yii;


class MasterContainerOwners extends BaseModel
{
    public static function tableName() { return '{{%master_container_owners}}'; }
    
    public function rules()
    {
        return [
            [['owner_name'], 'required'],
            [['owner_contact', 'owner_email'], 'string', 'max' => 100],
           
        ];
    }
}