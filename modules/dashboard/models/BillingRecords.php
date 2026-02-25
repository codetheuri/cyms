<?php

namespace dashboard\models;

use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;
use Yii;
use auth\models\User;

class BillingRecords extends BaseModel
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
            [['visit_id'], 'required'],
            [['visit_id', 'created_at', 'updated_at', 'requested_by', 'requested_at'], 'integer'],
            [
                [
                    'tariff_rate',
                    'storage_total',
                    'repair_total',
                    'lift_charges',
                    'grand_total',
                    'total_paid',
                    'balance',
                    'discount_amount',
                    'exchange_rate',
                    'foreign_grand_total',
                    'foreign_balance' // <-- NEW COLUMNS
                ],
                'number'
            ],
            [['status', 'approval_status', 'requester_note', 'rejection_reason', 'authorized_by'], 'string'],
            [['invoice_number', 'atl_number'], 'string', 'max' => 50],
            [['currency'], 'string', 'max' => 3], // <-- NEW COLUMN
            [['visit_id'], 'unique'],
            [['storage_days'], 'number'],
            [['discount_amount'], 'number', 'min' => 0],
            [['requester_note'], 'safe'],
            [['discount_amount'], 'validateDiscount'],
            [['agreement_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf, jpg, png, jpeg', 'maxSize' => 5 * 1024 * 1024],
        ];
    }

    public function attributeLabels()
    {
        return [
            'atl_number' => 'ATL Number (Auth to Leave)',
            'authorized_by' => 'Supervisor Name',
            'currency' => 'Billing Currency',
            'exchange_rate' => 'USD/KES Exchange Rate',
        ];
    }

    public function getRequester()
    {
        return $this->hasOne(User::class, ['user_id' => 'requested_by']);
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

  public function validateDiscount($attribute, $params)
    {
        if (!$this->hasErrors()) {
            // Everything is calculated strictly in KES
            $subTotalKes = ($this->storage_days * $this->tariff_rate) + $this->repair_total + $this->lift_charges;

            if ($this->$attribute > $subTotalKes) {
                $exRate = $this->exchange_rate > 0 ? $this->exchange_rate : 1.00;
                $displayTotal = ($this->currency === 'USD') ? ($subTotalKes / $exRate) : $subTotalKes;
                
                $this->addError($attribute, 'Discount cannot exceed the Total Bill (' . number_format($displayTotal, 2) . ' ' . $this->currency . ').');
            }
        }
    }

    public function recalculateBalance()
    {
        $visit = $this->visit;
        if (!$visit) return false;

        $clientCurrency = $visit->containerOwner ? $visit->containerOwner->billing_currency : 'KES';
        $this->currency = $clientCurrency; 

        // =========================================================
        // 1. EXCHANGE RATE
        // =========================================================
        if (in_array($this->status, ['PAID', 'CREDIT', 'GATE_OUT']) && $this->exchange_rate > 1) {
            // Keep locked rate
        } else {
            $this->exchange_rate = class_exists('\dashboard\hooks\Currency') 
                ? \dashboard\hooks\Currency::getUsdToKesRate() 
                : (float) Yii::$app->config->get('fallback_exchange_rate', 130.00); 
            if ($this->exchange_rate <= 0) $this->exchange_rate = 130.00;
        }

        // =========================================================
        // 2. CALCULATE STORAGE DAYS
        // =========================================================
        if ($visit->date_in) {
            $endTime = ($visit->status === 'GATE_OUT' && $visit->date_out)
                ? strtotime($visit->date_out . ' ' . ($visit->time_out ?: '23:59:59'))
                : time(); 

            $startStr = $visit->date_in . ' ' . ($visit->time_in ?: '00:00:00');
            $diffSeconds = $endTime - strtotime($startStr);
            $this->storage_days = ($diffSeconds < 0) ? 1 : (floor($diffSeconds / 86400) + 1);
        }

        // =========================================================
        // 3. PRICING LOGIC (STRICTLY KES SOURCE OF TRUTH)
        // =========================================================
        if ($visit->shipping_line_id == 9) {
            $this->tariff_rate = 0;       
            $this->lift_charges = 0;      
            $this->storage_total = 2000; // Flat KES
        } else {
            // If tariff rate is missing, fetch the KES Master
            if ($this->tariff_rate <= 0) {
                $baseKesRate = (float) Yii::$app->config->get('storage_rate_per_day');
                
                if ($visit->containerOwner) {
                    $clientRate = \dashboard\models\ClientRates::findOne(['owner_id' => $visit->containerOwner->owner_id, 'container_type_id' => $visit->container_type_id]);
                    if ($clientRate && $clientRate->daily_rate > 0) $baseKesRate = (float) $clientRate->daily_rate;
                } elseif ($visit->containerType && $visit->containerType->daily_rate > 0) {
                    $baseKesRate = (float) $visit->containerType->daily_rate;
                }
                
                // ALWAYS STORE KES IN DATABASE!
                $this->tariff_rate = $baseKesRate; 
            }
            $this->storage_total = $this->storage_days * $this->tariff_rate;
        }
        
        // =========================================================
        // 4. REPAIR COSTS
        // =========================================================
        $survey = \dashboard\models\ContainerSurveys::findOne(['visit_id' => $this->visit_id]);
        $this->repair_total = ($survey && $survey->bill_repairs) ? (float) $survey->getSurveyDamages()->sum('total_cost') : 0;

        // =========================================================
        // 5. SUBTOTALS & DISCOUNT (KES)
        // =========================================================
        $subTotalKes = $this->storage_total + $this->repair_total + $this->lift_charges;
        
        if ($this->discount_amount > $subTotalKes) {
            $this->discount_amount = $subTotalKes; // Cap discount
        }

        // =========================================================
        // 6. TOTALS & BALANCES (WITH ROUNDING TO NEAREST 10)
        // =========================================================
        $calculatedGrandTotalKes = $subTotalKes - $this->discount_amount;
        
        // Round KES to nearest 10 (e.g., 16484.46 becomes 16480, 16486 becomes 16490)
        $this->grand_total = round($calculatedGrandTotalKes, -1);
        
        // Convert to USD specifically for the foreign column
        $this->foreign_grand_total = ($this->currency === 'USD') ? round($this->grand_total / $this->exchange_rate, 2) : 0;

        $this->total_paid = (float) $this->getPayments()->sum('amount'); 
        $this->balance = $this->grand_total - $this->total_paid;
        
        $this->foreign_balance = ($this->currency === 'USD') ? round($this->balance / $this->exchange_rate, 2) : 0;

        // =========================================================
        // 7. STATUS 
        // =========================================================
        if ($this->status !== 'CREDIT') {
            if ($this->balance <= 0.01) {
                $this->status = 'PAID';
                $this->balance = 0;
                if ($this->currency === 'USD') $this->foreign_balance = 0;
            } elseif ($this->total_paid > 0) {
                $this->status = 'PARTIAL';
            } else {
                $this->status = 'UNPAID';
            }
        }

        return $this->save(false);
    }
}
