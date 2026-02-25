<?php

namespace admin\models\static;

use Yii;

class Tariff extends \yii\base\Model
{
    // --- BASE TARIFF FIELDS (KES) ---
    public $storage_rate_per_day;
    public $lift_on_charges;
    public $lift_off_charges;
    public $currency_code; 
    public $tax_percentage; 

    // --- SECONDARY TARIFF FIELDS (USD) ---
    public $usd_storage_rate_per_day;
    public $usd_lift_on_charges;
    public $usd_lift_off_charges;
    
    // --- NEW: FALLBACK EXCHANGE RATE ---
    public $fallback_exchange_rate;

    const CATEGORY = 'TARIFF';

   public function __construct()
    {
        // FIX: Check if one of the NEW keys is missing. 
        // If missing, it triggers createKeys() which safely adds only the missing rows.
        if (is_null(Yii::$app->config->get('fallback_exchange_rate'))) {
            $this->createKeys();
        }

        // Hydrate from config (Load current KES/Base values)
        $this->storage_rate_per_day = Yii::$app->config->get('storage_rate_per_day');
        $this->lift_on_charges      = Yii::$app->config->get('lift_on_charges');
        $this->lift_off_charges     = Yii::$app->config->get('lift_off_charges');
        $this->currency_code        = Yii::$app->config->get('currency_code');
        $this->tax_percentage       = Yii::$app->config->get('tax_percentage');

        // Hydrate USD values
        $this->usd_storage_rate_per_day = Yii::$app->config->get('usd_storage_rate_per_day');
        $this->usd_lift_on_charges      = Yii::$app->config->get('usd_lift_on_charges');
        $this->usd_lift_off_charges     = Yii::$app->config->get('usd_lift_off_charges');
        
        // Load fallback rate
        $this->fallback_exchange_rate   = Yii::$app->config->get('fallback_exchange_rate');

        parent::__construct();
    }

    public function rules()
    {
        return [
            [['storage_rate_per_day', 'lift_on_charges', 'lift_off_charges', 'currency_code', 
              'usd_storage_rate_per_day', 'usd_lift_on_charges', 'usd_lift_off_charges', 'fallback_exchange_rate'], 'required'],
            
            [['storage_rate_per_day', 'lift_on_charges', 'lift_off_charges', 'tax_percentage',
              'usd_storage_rate_per_day', 'usd_lift_on_charges', 'usd_lift_off_charges', 'fallback_exchange_rate'], 'number', 'min' => 0],
            
            [['currency_code'], 'string', 'length' => 3],
        ];
    }

    public function createKeys()
    {
        return Yii::$app->config->add(
            [
                ['key' => 'currency_code',        'default' => 'KES',  'category' => self::CATEGORY, 'disposition' => 0, 'label' => 'Base Currency Code'],
                
                ['key' => 'fallback_exchange_rate', 'default' => '130.00', 'category' => self::CATEGORY, 'disposition' => 1, 'label' => 'Fallback USD/KES Rate'],
                
                ['key' => 'storage_rate_per_day', 'default' => '1000', 'category' => self::CATEGORY, 'disposition' => 2, 'label' => 'Storage Rate (Base)'],
                ['key' => 'lift_on_charges',      'default' => '1500', 'category' => self::CATEGORY, 'disposition' => 3, 'label' => 'Lift On Charge (Base)'],
                ['key' => 'lift_off_charges',     'default' => '1500', 'category' => self::CATEGORY, 'disposition' => 4, 'label' => 'Lift Off Charge (Base)'],
                
                ['key' => 'usd_storage_rate_per_day', 'default' => '10', 'category' => self::CATEGORY, 'disposition' => 5, 'label' => 'Storage Rate (USD)'],
                ['key' => 'usd_lift_on_charges',      'default' => '15', 'category' => self::CATEGORY, 'disposition' => 6, 'label' => 'Lift On Charge (USD)'],
                ['key' => 'usd_lift_off_charges',     'default' => '15', 'category' => self::CATEGORY, 'disposition' => 7, 'label' => 'Lift Off Charge (USD)'],
                
                ['key' => 'tax_percentage',       'default' => '16',   'category' => self::CATEGORY, 'disposition' => 8, 'label' => 'VAT Percentage (%)'],
            ]
        );
    }

    public function attributeLabels()
    {
        return [
            'currency_code'            => 'Base Currency (e.g., KES)',
            'fallback_exchange_rate'   => 'Fallback USD to KES Rate',
            'storage_rate_per_day'     => 'Storage Rate (Base/Day)',
            'lift_on_charges'          => 'Lift On Charge (Base)',
            'lift_off_charges'         => 'Lift Off Charge (Base)',
            'usd_storage_rate_per_day' => 'Storage Rate (USD/Day)',
            'usd_lift_on_charges'      => 'Lift On Charge (USD)',
            'usd_lift_off_charges'     => 'Lift Off Charge (USD)',
            'tax_percentage'           => 'VAT Percentage',
        ];
    }

    public static function layout(): array
    {
        return [
            'currency_code'            => 'col-lg-6 col-12 mb-3 border-bottom pb-2 text-primary fw-bold',
            'fallback_exchange_rate'   => 'col-lg-6 col-12 mb-3 border-bottom pb-2 text-danger fw-bold',
            
            'storage_rate_per_day'     => 'col-lg-4 col-12',
            'lift_on_charges'          => 'col-lg-4 col-12',
            'lift_off_charges'         => 'col-lg-4 col-12',
            
            'usd_storage_rate_per_day' => 'col-lg-4 col-12 mt-3',
            'usd_lift_on_charges'      => 'col-lg-4 col-12 mt-3',
            'usd_lift_off_charges'     => 'col-lg-4 col-12 mt-3',
            
            'tax_percentage'           => 'col-lg-12 col-12 mt-4',
        ];
    }
}