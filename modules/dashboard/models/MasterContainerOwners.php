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

    public function getBillingRecords()
    {
        // Link: Owner -> Visits -> BillingRecords
        return $this->hasMany(BillingRecords::class, ['visit_id' => 'visit_id'])
                    ->via('containerVisits');
    }

    /**
     * Ensure you also have this relation to visits
     */
    public function getContainerVisits()
    {
        return $this->hasMany(ContainerVisits::class, ['container_owner_id' => 'owner_id']);
    }
}