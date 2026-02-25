<?php
namespace dashboard\models;

use Yii;

class MasterContainerOwners extends BaseModel
{
    public static function tableName() 
    { 
        return '{{%master_container_owners}}'; 
    }
    
    public function rules()
    {
        return [
            [['owner_name'], 'required'],
            [['owner_contact', 'owner_email'], 'string', 'max' => 100],
            
            // --- NEW: Currency Validation ---
            [['billing_currency'], 'string', 'max' => 3],
            [['billing_currency'], 'default', 'value' => 'KES'], // Default to KES if left blank
            [['billing_currency'], 'in', 'range' => ['KES', 'USD'], 'message' => 'Currency must be KES or USD.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'owner_name' => 'Owner / Company Name',
            'owner_contact' => 'Contact Number',
            'owner_email' => 'Email Address',
            'billing_currency' => 'Billing Currency',
        ];
    }

    public function getBillingRecords()
    {
        // Link: Owner -> Visits -> BillingRecords
        return $this->hasMany(BillingRecords::class, ['visit_id' => 'visit_id'])
                    ->via('containerVisits');
    }

    public function getContainerVisits()
    {
        return $this->hasMany(ContainerVisits::class, ['container_owner_id' => 'owner_id']);
    }
}