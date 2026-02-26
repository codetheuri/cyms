<?php

namespace dashboard\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use auth\models\User; // Assuming this is your User model based on previous code

class BillingReversals extends BaseModel // Change to \yii\db\ActiveRecord if BaseModel doesn't exist
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%billing_reversals}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bill_id', 'old_invoice_number', 'old_grand_total', 'old_currency', 'reversal_reason', 'reversed_by', 'snapshot_data'], 'required'],
            [['bill_id', 'reversed_by', 'created_at', 'updated_at', 'is_deleted'], 'integer'],
            [['old_grand_total'], 'number'],
            [['reversal_reason', 'snapshot_data'], 'string'],
            [['old_invoice_number'], 'string', 'max' => 50],
            [['old_currency'], 'string', 'max' => 3],
            [['bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => BillingRecords::class, 'targetAttribute' => ['bill_id' => 'bill_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bill_id' => 'Bill ID',
            'old_invoice_number' => 'Old Invoice Number',
            'old_grand_total' => 'Old Grand Total',
            'old_currency' => 'Old Currency',
            'reversal_reason' => 'Reversal Reason',
            'reversed_by' => 'Authorized By',
            'snapshot_data' => 'Snapshot Data',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Reversed At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets the associated Billing Record.
     */
    public function getBill()
    {
        return $this->hasOne(BillingRecords::class, ['bill_id' => 'bill_id']);
    }

    /**
     * Gets the User who authorized the reversal.
     * Note: Change 'id' to 'user_id' if your users table primary key is different.
     */
    public function getReverser()
    {
        return $this->hasOne(User::class, ['id' => 'reversed_by']);
    }

    /**
     * Helper method to easily retrieve the snapshot data as an array.
     */
    public function getSnapshotArray()
    {
        return json_decode($this->snapshot_data, true);
    }
}