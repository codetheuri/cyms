<?php

namespace admin\models\static;

use Yii;

class Tariff extends \yii\base\Model
{
    // --- TARIFF FIELDS ---
    public $storage_rate_per_day;
    public $lift_on_charges;
    public $lift_off_charges;
    public $currency_code;
    public $tax_percentage; // VAT

    const CATEGORY = 'TARIFF';

    public function __construct()
    {
        // Initialize keys if they don't exist in DB
        if (is_null(Yii::$app->config->get('storage_rate_per_day'))) {
            $this->createKeys();
        }

        // Hydrate from config (Load current values)
        $this->storage_rate_per_day = Yii::$app->config->get('storage_rate_per_day');
        $this->lift_on_charges      = Yii::$app->config->get('lift_on_charges');
        $this->lift_off_charges     = Yii::$app->config->get('lift_off_charges');
        $this->currency_code        = Yii::$app->config->get('currency_code');
        $this->tax_percentage       = Yii::$app->config->get('tax_percentage');

        parent::__construct();
    }

    public function rules()
    {
        return [
            // All fields are required for billing to work correctly
            [['storage_rate_per_day', 'lift_on_charges', 'lift_off_charges', 'currency_code'], 'required'],
            
            // Numeric validation ensures calculations won't break
            [['storage_rate_per_day', 'lift_on_charges', 'lift_off_charges', 'tax_percentage'], 'number', 'min' => 0],
            
            // Currency code format (e.g., KES, USD)
            [['currency_code'], 'string', 'length' => 3],
            [['currency_code'], 'match', 'pattern' => '/^[A-Z]+$/', 'message' => 'Currency code must be 3 uppercase letters (e.g., KES).'],
        ];
    }

    public function createKeys()
    {
        return Yii::$app->config->add(
            [
                ['key' => 'storage_rate_per_day', 'default' => '1000', 'category' => self::CATEGORY, 'disposition' => 0, 'label' => 'Storage Rate (Per Day)'],
                ['key' => 'lift_on_charges',      'default' => '1500', 'category' => self::CATEGORY, 'disposition' => 1, 'label' => 'Lift On Charge'],
                ['key' => 'lift_off_charges',     'default' => '1500', 'category' => self::CATEGORY, 'disposition' => 2, 'label' => 'Lift Off Charge'],
                ['key' => 'currency_code',        'default' => 'KES',  'category' => self::CATEGORY, 'disposition' => 3, 'label' => 'Currency Code'],
                ['key' => 'tax_percentage',       'default' => '16',   'category' => self::CATEGORY, 'disposition' => 4, 'label' => 'VAT Percentage (%)'],
            ]
        );
    }

    public function attributeLabels()
    {
        return [
            'storage_rate_per_day' => 'Storage Rate (Per Day)',
            'lift_on_charges'      => 'Lift On Charge (Loading)',
            'lift_off_charges'     => 'Lift Off Charge (Offloading)',
            'currency_code'        => 'Currency Code (e.g., KES)',
            'tax_percentage'       => 'VAT Percentage',
        ];
    }

    // Layout helper for the view
    public static function layout(): array
    {
        return [
            'storage_rate_per_day' => 'col-lg-6 col-12',
            'currency_code'        => 'col-lg-6 col-12',
            'lift_on_charges'      => 'col-lg-6 col-12',
            'lift_off_charges'     => 'col-lg-6 col-12',
            'tax_percentage'       => 'col-lg-6 col-12',
        ];
    }
}