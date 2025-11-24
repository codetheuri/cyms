<?php

namespace dashboard\models\static;

use Yii;

class Email extends \yii\base\Model
{
    public $admin_email;
    public $support_email;
    public $sender_email;
    public $sender_name;
    public $smtp_host;
    public $smtp_port;
    public $smtp_user;
    public $smtp_password;
    public $email_encryption;
    const CATEGORY = 'EMAIL';

    public function __construct()
    {
        if (is_null(Yii::$app->config->get('admin_email'))) {
            $this->createKeys();
        }
        $this->admin_email = Yii::$app->config->get('admin_email');
        $this->support_email = Yii::$app->config->get('support_email');
        $this->sender_email = Yii::$app->config->get('sender_email');
        $this->sender_name = Yii::$app->config->get('sender_name');
        $this->smtp_host = Yii::$app->config->get('smtp_host');
        $this->smtp_port = Yii::$app->config->get('smtp_port');
        $this->smtp_user = Yii::$app->config->get('smtp_user');
        $this->smtp_password = Yii::$app->config->get('smtp_password');
        $this->email_encryption = Yii::$app->config->get('email_encryption');
        parent::__construct();
    }
    public function rules()
    {
        return [
            [['sender_name', 'admin_email', 'support_email', 'sender_email', 'smtp_host', 'smtp_password', 'smtp_user', 'smtp_port', 'email_encryption'], 'required'],
            [['admin_email', 'support_email', 'sender_email'], 'email', 'message' => 'Invalid email'],
            ['smtp_port', 'integer', 'max' => 9999, 'message' => 'Invalid port number'],
            [['sender_name', 'admin_email', 'support_email', 'sender_email', 'smtp_host', 'smtp_password', 'smtp_user', 'email_encryption'], 'string'],
        ];
    }
    public function createKeys()
    {
        return Yii::$app->config->add(
            [
                ['key'   => 'admin_email', 'default' => 'admin@crackit.co.ke', 'category' => self::CATEGORY, 'disposition' => 2, 'label' => 'Administrative Email'],
                ['key'   => 'support_email', 'default' => 'support@crackit.co.ke', 'category' => self::CATEGORY, 'disposition' => 3, 'label' => 'Support Email'],
                ['key'   => 'sender_email', 'default' => 'info@crackit.co.ke', 'category' => self::CATEGORY, 'disposition' => 3, 'label' => 'Sender Email'],
                ['key'   => 'sender_name', 'default' => Yii::$app->name . ' Support', 'category' => self::CATEGORY, 'disposition' => 1, 'label' => 'Mail From'],
                ['key'   => 'smtp_host', 'default' => 'mail.crackit.co.ke', 'category' => self::CATEGORY, 'disposition' => 4, 'label' => 'SMTP Server'],
                ['key'   => 'smtp_port', 'default' => '587', 'category' => self::CATEGORY, 'disposition' => 5, 'label' => 'Port'],
                ['key'   => 'smtp_user', 'default' => 'noreply@crackit.co.ke', 'category' => self::CATEGORY, 'disposition' => 6, 'label' => 'SMTP Username'],
                ['key'   => 'smtp_password', 'default' => 'emailpassword', 'category' => self::CATEGORY, 'disposition' => 7, 'label' => 'Password', 'input_type' => 'passwordInput'],
                ['key'   => 'email_encryption', 'default' => 'ssl', 'category' => self::CATEGORY, 'disposition' => 8, 'label' => 'Encryption', 'input_type' => 'dropDownList', 'input_preload' => serialize(['ssl' => 'SSL', 'tls' => 'TLS'])],
            ]
        );
    }
    public function attributeLabels()
    {
        return [
            'admin_email' => Yii::$app->config->get('admin_email', true),
            'support_email' => Yii::$app->config->get('support_email', true),
            'sender_email' => Yii::$app->config->get('sender_email', true),
            'sender_name' => Yii::$app->config->get('sender_name', true),
            'smtp_host' => Yii::$app->config->get('smtp_host', true),
            'smtp_port' => Yii::$app->config->get('smtp_port', true),
            'smtp_user' => Yii::$app->config->get('smtp_user', true),
            'smtp_password' => Yii::$app->config->get('smtp_password', true),
            'email_encryption' => Yii::$app->config->get('email_encryption', true),
        ];
    }

    public static function layout(): array
    {
        return [
            'sender_name'   => 'col-lg-6 col-12',
            'admin_email'   => 'col-lg-6 col-12',
            'support_email' => 'col-lg-6 col-12',
            'sender_email'  => 'col-lg-6 col-12',
            'smtp_host'     => 'col-lg-6 col-12',
            'smtp_port'     => 'col-lg-3 col-6',
            'smtp_user'     => 'col-lg-6 col-12',
            'smtp_password' => 'col-lg-6 col-12',
            'email_encryption' => 'col-lg-3 col-6',
        ];
    }
}
