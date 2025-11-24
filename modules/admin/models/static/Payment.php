<?php

namespace dashboard\models\static;

use Yii;

class Payment extends \yii\base\Model
{
    // PayPal
    public $paypal_mode;
    public $paypal_client_id;
    public $paypal_secret;

    // Stripe
    public $stripe_publishable_key;
    public $stripe_secret_key;

    // M-Pesa
    public $mpesa_consumer_key;
    public $mpesa_consumer_secret;
    public $mpesa_shortcode;
    public $mpesa_passkey;
    public $mpesa_environment; // sandbox / production

    // Pesapal
    public $pesapal_consumer_key;
    public $pesapal_consumer_secret;
    public $pesapal_callback_url;
    public $pesapal_ipn_url;
    public $pesapal_env;       // live / sandbox
    public $pesapal_ipn_id;
    public $pesapal_cancel_url;

    const CATEGORY = 'PAYMENT';

    public function __construct()
    {
        if (is_null(Yii::$app->config->get('paypal_mode'))) {
            $this->createKeys();
        }

        // PayPal
        $this->paypal_mode            = Yii::$app->config->get('paypal_mode');
        $this->paypal_client_id       = Yii::$app->config->get('paypal_client_id');
        $this->paypal_secret          = Yii::$app->config->get('paypal_secret');

        // Stripe
        $this->stripe_publishable_key = Yii::$app->config->get('stripe_publishable_key');
        $this->stripe_secret_key      = Yii::$app->config->get('stripe_secret_key');

        // M-Pesa
        $this->mpesa_consumer_key     = Yii::$app->config->get('mpesa_consumer_key');
        $this->mpesa_consumer_secret  = Yii::$app->config->get('mpesa_consumer_secret');
        $this->mpesa_shortcode        = Yii::$app->config->get('mpesa_shortcode');
        $this->mpesa_passkey          = Yii::$app->config->get('mpesa_passkey');
        $this->mpesa_environment      = Yii::$app->config->get('mpesa_environment');

        // Pesapal
        $this->pesapal_consumer_key   = Yii::$app->config->get('pesapal_consumer_key');
        $this->pesapal_consumer_secret = Yii::$app->config->get('pesapal_consumer_secret');
        $this->pesapal_callback_url   = Yii::$app->config->get('pesapal_callback_url');
        $this->pesapal_ipn_url        = Yii::$app->config->get('pesapal_ipn_url');
        $this->pesapal_env            = Yii::$app->config->get('pesapal_env');
        $this->pesapal_ipn_id         = Yii::$app->config->get('pesapal_ipn_id');
        $this->pesapal_cancel_url     = Yii::$app->config->get('pesapal_cancel_url');

        parent::__construct();
    }

    public function rules()
    {
        return [
            [['paypal_mode', 'paypal_client_id', 'paypal_secret'], 'safe'],
            [['stripe_publishable_key', 'stripe_secret_key'], 'safe'],
            [['mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_shortcode', 'mpesa_passkey', 'mpesa_environment'], 'safe'],
            [['pesapal_consumer_key', 'pesapal_consumer_secret', 'pesapal_callback_url', 'pesapal_ipn_url', 'pesapal_env', 'pesapal_ipn_id', 'pesapal_cancel_url'], 'safe'],
        ];
    }

    public function createKeys()
    {
        return Yii::$app->config->add([
            // PayPal
            ['key' => 'paypal_mode', 'default' => 'sandbox', 'category' => self::CATEGORY, 'disposition' => 0, 'label' => 'PayPal Mode', 'input_type' => 'dropDownList', 'input_preload' => serialize(['sandbox' => 'Sandbox', 'live' => 'Live'])],
            ['key' => 'paypal_client_id', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 1, 'label' => 'PayPal Client ID'],
            ['key' => 'paypal_secret', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 2, 'label' => 'PayPal Secret'],

            // Stripe
            ['key' => 'stripe_publishable_key', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 3, 'label' => 'Stripe Publishable Key'],
            ['key' => 'stripe_secret_key', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 4, 'label' => 'Stripe Secret Key'],

            // M-Pesa
            ['key' => 'mpesa_consumer_key', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 5, 'label' => 'M-Pesa Consumer Key'],
            ['key' => 'mpesa_consumer_secret', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 6, 'label' => 'M-Pesa Consumer Secret'],
            ['key' => 'mpesa_shortcode', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 7, 'label' => 'M-Pesa Shortcode'],
            ['key' => 'mpesa_passkey', 'default' => '', 'category' => self::CATEGORY, 'disposition' => 8, 'label' => 'M-Pesa Passkey'],
            ['key' => 'mpesa_environment', 'default' => 'sandbox', 'category' => self::CATEGORY, 'disposition' => 9, 'label' => 'M-Pesa Environment', 'input_type' => 'dropDownList', 'input_preload' => serialize(['sandbox' => 'Sandbox', 'production' => 'Production'])],

            // Pesapal
            ['key' => 'pesapal_consumer_key',    'default' => getenv('PESAPAL_CONSUMER_KEY') ?: '', 'category' => self::CATEGORY, 'disposition' => 10, 'label' => 'Pesapal Consumer Key'],
            ['key' => 'pesapal_consumer_secret', 'default' => getenv('PESAPAL_CONSUMER_SECRET') ?: '', 'category' => self::CATEGORY, 'disposition' => 11, 'label' => 'Pesapal Consumer Secret'],
            ['key' => 'pesapal_callback_url',    'default' => getenv('PESAPAL_CALLBACK_URL') ?: '', 'category' => self::CATEGORY, 'disposition' => 12, 'label' => 'Pesapal Callback URL'],
            ['key' => 'pesapal_ipn_url',         'default' => getenv('PESAPAL_IPN_URL') ?: '', 'category' => self::CATEGORY, 'disposition' => 13, 'label' => 'Pesapal IPN URL'],
            ['key' => 'pesapal_env',             'default' => getenv('PESAPAL_ENV') ?: 'sandbox', 'category' => self::CATEGORY, 'disposition' => 14, 'label' => 'Pesapal Environment', 'input_type' => 'dropDownList', 'input_preload' => serialize(['sandbox' => 'Sandbox', 'live' => 'Live'])],
            ['key' => 'pesapal_ipn_id',          'default' => getenv('PESAPAL_IPN_ID') ?: '', 'category' => self::CATEGORY, 'disposition' => 15, 'label' => 'Pesapal IPN ID'],
            ['key' => 'pesapal_cancel_url',      'default' => getenv('PESAPAL_CANCEL_URL') ?: '', 'category' => self::CATEGORY, 'disposition' => 16, 'label' => 'Pesapal Cancel URL'],
        ]);
    }

    public function attributeLabels()
    {
        return [
            // PayPal
            'paypal_mode'          => 'PayPal Mode',
            'paypal_client_id'     => 'PayPal Client ID',
            'paypal_secret'        => 'PayPal Secret',

            // Stripe
            'stripe_publishable_key' => 'Stripe Publishable Key',
            'stripe_secret_key'      => 'Stripe Secret Key',

            // M-Pesa
            'mpesa_consumer_key'    => 'M-Pesa Consumer Key',
            'mpesa_consumer_secret' => 'M-Pesa Consumer Secret',
            'mpesa_shortcode'       => 'M-Pesa Shortcode',
            'mpesa_passkey'         => 'M-Pesa Passkey',
            'mpesa_environment'     => 'M-Pesa Environment',

            // Pesapal
            'pesapal_consumer_key'    => 'Pesapal Consumer Key',
            'pesapal_consumer_secret' => 'Pesapal Consumer Secret',
            'pesapal_callback_url'    => 'Pesapal Callback URL',
            'pesapal_ipn_url'         => 'Pesapal IPN URL',
            'pesapal_env'             => 'Pesapal Environment',
            'pesapal_ipn_id'          => 'Pesapal IPN ID',
            'pesapal_cancel_url'      => 'Pesapal Cancel URL',
        ];
    }

    public static function layout(): array
    {
        return [
            // PayPal
            'paypal_mode'          => 'col-lg-6 col-12',
            'paypal_client_id'     => 'col-lg-6 col-12',
            'paypal_secret'        => 'col-lg-6 col-12',

            // Stripe
            'stripe_publishable_key' => 'col-lg-6 col-12',
            'stripe_secret_key'      => 'col-lg-6 col-12',

            // M-Pesa
            'mpesa_consumer_key'    => 'col-lg-6 col-12',
            'mpesa_consumer_secret' => 'col-lg-6 col-12',
            'mpesa_shortcode'       => 'col-lg-6 col-12',
            'mpesa_passkey'         => 'col-lg-6 col-12',
            'mpesa_environment'     => 'col-lg-6 col-12',

            // Pesapal
            'pesapal_consumer_key'    => 'col-lg-6 col-12',
            'pesapal_consumer_secret' => 'col-lg-6 col-12',
            'pesapal_callback_url'    => 'col-lg-12 col-12',
            'pesapal_ipn_url'         => 'col-lg-12 col-12',
            'pesapal_env'             => 'col-lg-6 col-12',
            'pesapal_ipn_id'          => 'col-lg-6 col-12',
            'pesapal_cancel_url'      => 'col-lg-12 col-12',
        ];
    }
}
