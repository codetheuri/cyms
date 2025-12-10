<?php

namespace dashboard\models;

use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;
use DateTime;
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
            [['tariff_rate', 'storage_total', 'repair_total', 'lift_charges', 'grand_total', 'total_paid', 'balance', 'discount_amount'], 'number'],
            [['status'], 'string'],
            [['invoice_number'], 'string', 'max' => 50],
            [['visit_id'], 'unique'],
            [['discount_amount'], 'number', 'min' => 0],
            [['discount_amount'], 'validateDiscount'],
            [['agreement_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, jpg, png,jpeg', 'maxSize' => 5 * 1024 * 1024],
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
        $visit = $this->visit;
        
        // --- FIX 1: AUTO-CALCULATE DAYS INSIDE MODEL ---
        if ($visit && $visit->date_in) {
            $start = new DateTime($visit->date_in);
            
            // If container has left, use date_out. If still in yard, use NOW.
            if ($visit->status === 'GATE_OUT' && $visit->date_out) {
                $end = new DateTime($visit->date_out);
            } else {
                $end = new DateTime(); // Now
            }
            
            $days = $start->diff($end)->days;
            
            // Logic: Even if it's 2 hours, charge for 1 day.
            if ($days < 1) $days = 1;
            
            $this->storage_days = $days;
        }
        // -----------------------------------------------

        // 2. GET RATE
        if ($visit && $visit->containerType) {
            $this->tariff_rate = (float) $visit->containerType->daily_rate;
        } else {
            $this->tariff_rate = (float) Yii::$app->config->get('storage_rate_per_day');
        }

        // 3. GET REPAIR COSTS
        $survey = ContainerSurveys::findOne(['visit_id' => $this->visit_id]);
        if ($survey && $survey->bill_repairs) {
            $this->repair_total = (float) $survey->getSurveyDamages()->sum('total_cost');
        } else {
            $this->repair_total = 0;
        }

        // 4. CALCULATE TOTALS
        $this->storage_total = (float)$this->storage_days * $this->tariff_rate;
        $subTotal = $this->storage_total + $this->repair_total;

        // Discount
        $discount = (float)$this->discount_amount;
        if ($discount > $subTotal) $discount = $subTotal;
         
        $this->grand_total = $subTotal - $discount;

        // 5. PAYMENTS & BALANCE
        $this->total_paid = (float) $this->getPayments()->sum('amount');
        $this->balance = $this->grand_total - $this->total_paid;

        // 6. STATUS UPDATE
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
    // public function recalculateBalance()
    // {
    //     // 1. GET RATE FROM CONTAINER TYPE
    //     $visit = $this->visit;
    //     if ($visit && $visit->containerType) {
    //         $this->tariff_rate = (float) $visit->containerType->daily_rate;
    //     } else {
    //         $this->tariff_rate = (float) Yii::$app->config->get('storage_rate_per_day');
    //     }

    //     // 2. GET REPAIR COSTS
    //     $survey = ContainerSurveys::findOne(['visit_id' => $this->visit_id]);
        
    //     // --- LOGIC FIX HERE ---
    //     if ($survey && $survey->bill_repairs) {
    //         // Only add cost if the flag is ON (1)
    //         $this->repair_total = (float) $survey->getSurveyDamages()->sum('total_cost');
    //     } else {
    //         // Otherwise cost is ZERO
    //         $this->repair_total = 0;
    //     }
        
    //     // DELETE THE LINE THAT WAS HERE. It was overwriting the logic above.

    //     // 3. CALCULATE TOTALS
    //     $this->storage_total = (float)$this->storage_days * $this->tariff_rate;

    //     $subTotal = $this->storage_total ;

    //     // Apply Discount
    //     $discount = (float)$this->discount_amount;
    //     if ($discount > $subTotal) $discount = $subTotal;
         
    //     $this->grand_total = $subTotal - $discount;

    //     // 4. PAYMENTS & BALANCE
    //     $this->total_paid = (float) $this->getPayments()->sum('amount');
    //     $this->balance = $this->grand_total - $this->total_paid;

    //     // 5. STATUS UPDATE
    //     if ($this->status !== 'CREDIT') {
    //         if ($this->balance <= 0.01) {
    //             $this->status = 'PAID';
    //             $this->balance = 0;
    //         } elseif ($this->total_paid > 0) {
    //             $this->status = 'PARTIAL';
    //         } else {
    //             $this->status = 'UNPAID';
    //         }
    //     }

    //     return $this->save(false);
    // }
    public function validateDiscount($attribute, $params)
    {
        if (!$this->hasErrors()) {
            // Calculate Subtotal (Storage + Repair + Lift)
            $subTotal = ($this->storage_days * $this->tariff_rate) + $this->repair_total + $this->lift_charges;

            if ($this->$attribute > $subTotal) {
                $this->addError($attribute, 'Discount cannot be more than the Total Bill (' . number_format($subTotal, 2) . ').');
            }
        }
    }
    // public function recalculateBalance()
    // {

    //     $survey = ContainerSurveys::findOne(['visit_id' => $this->visit_id]);
    //     if ($survey) {

    //         $this->repair_total = (float) $survey->getSurveyDamages()->sum('total_cost');
    //     } else {
    //         $this->repair_total = 0;
    //     }

    //     $this->storage_total = (float)$this->storage_days * (float)$this->tariff_rate;
    //     $this->grand_total   = $this->storage_total + $this->repair_total + (float)$this->lift_charges;


    //     $this->total_paid = (float) $this->getPayments()->sum('amount');


    //     $this->balance = $this->grand_total - $this->total_paid;


    //     if ($this->status !== 'CREDIT') {

    //         if ($this->balance <= 0.01) {
    //             $this->status = 'PAID';
    //             $this->balance = 0;
    //         } elseif ($this->total_paid > 0) {
    //             $this->status = 'PARTIAL';
    //         } else {
    //             $this->status = 'UNPAID';
    //         }
    //     }

    //     return $this->save(false);
    // }
}
