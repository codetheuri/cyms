<?php

namespace dashboard\models;

use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;

use Yii;

class BillingRecords extends  BaseModel
{
    public $agreement_file;
    public static function tableName()
    {
        return '{{%billing_records}}';
    }
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    public function rules()
    {
        return [
            [['visit_id', 'authorized_by'], 'required'],
            [['visit_id', 'storage_days', 'created_at', 'updated_at'], 'integer'],
            [['tariff_rate', 'storage_total', 'repair_total', 'lift_charges', 'grand_total', 'total_paid', 'balance'], 'number'],
            [['status'], 'string'],
            [['invoice_number'], 'string', 'max' => 50],
            [['visit_id'], 'unique'],
            [['agreement_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, jpg, png', 'maxSize' => 5 * 1024 * 1024],
        ];
    }

    public function getPayments()
    {
        return $this->hasMany(BillingPayments::class, ['bill_id' => 'bill_id']);
    }

    public function getVisit()
    {
        return $this->hasOne(ContainerVisits::class, ['visit_id' => 'visit_id']);
    }
    public function getBill()
    {
        return $this->hasOne(BillingRecords::class, ['bill_id' => 'bill_id']);
    }
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord && empty($this->invoice_number)) {
                $this->invoice_number = 'INV-' . date('ym') . '-' . rand(1000, 9999);
            }
            return true;
        }
        return false;
    }


    public function uploadAgreement()
    {
        $this->agreement_file = UploadedFile::getInstance($this, 'agreement_file');
        if ($this->agreement_file) {
            $path = Yii::getAlias('@webroot') . '/uploads/agreements/';
            if (!is_dir($path)) mkdir($path, 0777, true);

            $fileName = 'AGR-' . $this->invoice_number . '.' . $this->agreement_file->extension;
            if ($this->agreement_file->saveAs($path . $fileName)) {
                $this->credit_agreement_path = 'uploads/agreements/' . $fileName;
                return true;
            }
        }
        return false;
    }
    public function recalculateBalance()
    {

        $survey = ContainerSurveys::findOne(['visit_id' => $this->visit_id]);
        if ($survey) {

            $this->repair_total = (float) $survey->getSurveyDamages()->sum('total_cost');
        } else {
            $this->repair_total = 0;
        }

        $this->storage_total = (float)$this->storage_days * (float)$this->tariff_rate;
        $this->grand_total   = $this->storage_total + $this->repair_total + (float)$this->lift_charges;


        $this->total_paid = (float) $this->getPayments()->sum('amount');


        $this->balance = $this->grand_total - $this->total_paid;


        if ($this->status !== 'CREDIT') {

            if ($this->balance <= 0.01) {
                $this->status = 'PAID';
                $this->balance = 0;
            } elseif ($this->total_paid > 0) {
                $this->status = 'PARTIAL';
            } else {
                $this->status = 'UNPAID';
            }
        }

        return $this->save(false);
    }
}
