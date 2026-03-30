<?php

namespace dashboard\models;

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
            [['amount'], 'number', 'min' => 1],
            [['transaction_date'], 'safe'],
            [['method'], 'string'],
            [['amount'], 'validateOverpayment'],
            [['reference'], 'string', 'max' => 100],
        ];
    }

    public function validateOverpayment($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $invoice = BillingRecords::findOne($this->bill_id);
            if ($invoice) {
                // IMPORTANT: Recalculate based on current date before validating,
                // otherwise we validate against a 'stale' balance from the DB
                $invoice->recalculateBalance(false); 
                
                if ($this->amount > ($invoice->balance + 0.01)) {
                    $this->addError($attribute, 'Payment cannot exceed the outstanding balance (' . number_format($invoice->balance, 2) . ').');
                }
            }
        }
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