<?php

namespace dashboard\models;

use Faker\Provider\Base;
use Yii;

class BillingPayments extends BaseModel
{
    public static function tableName()
    {
        return '{{%billing_payments}}';
    }

    public function rules()
    {
        return [
            [['bill_id', 'amount', 'transaction_date', 'method'], 'required'],
            [['bill_id',  'created_at'], 'integer'],
            [['amount'], 'number'],
            [['transaction_date'], 'safe'],
            [['method'], 'string'],
            [['reference'], 'string', 'max' => 100],
        ];
    }

   
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->updateInvoice();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->updateInvoice();
    }

    protected function updateInvoice()
    {
        $invoice = BillingRecords::findOne($this->bill_id);
        if ($invoice) {
            $invoice->recalculateBalance();
        }
    }
      public function getBill()
    {
        return $this->hasOne(BillingRecords::class, ['bill_id' => 'bill_id']);
    }
}