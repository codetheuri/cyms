<?php

namespace admin\models\static;

use Yii;
use yii\web\UploadedFile;

class General extends \yii\base\Model
{
    public $organization_name;
    public $organization_initials;
    public $physical_address;
    public $email_address;
    public $primary_mobile_number;
    
    public $site_logo; // Stores the STRING path (e.g., 'uploads/settings/logo.png')
    public $logo_file; // Stores the UPLOADED FILE object

    public $country;
    public $website;

    const CATEGORY = 'GENERAL';
    const UPLOAD_BASE_DIR = 'uploads/settings/';

    public function __construct()
    {
        if (is_null(Yii::$app->config->get('organization_name'))) {
            $this->createKeys();
        }

        $this->organization_name        = Yii::$app->config->get('organization_name');
        $this->organization_initials    = Yii::$app->config->get('organization_initials');
        $this->physical_address         = Yii::$app->config->get('physical_address');
        $this->email_address            = Yii::$app->config->get('email_address');
        $this->primary_mobile_number    = Yii::$app->config->get('primary_mobile_number');
        $this->country                  = Yii::$app->config->get('country');
        $this->website                  = Yii::$app->config->get('website');
        $this->site_logo                = Yii::$app->config->get('site_logo');

        parent::__construct();
    }

    public function rules()
    {
        return [
            [['organization_name', 'organization_initials', 'physical_address', 'primary_mobile_number', 'email_address'], 'required'],
            [['email_address'], 'email', 'message' => 'Invalid email'],
            [['website'], 'url'],
            
            // Validation for the FILE input
            [['logo_file'], 'file', 'skipOnEmpty' => true, 'extensions' => ['png', 'jpg', 'jpeg'], 'maxSize' => 3 * 1024 * 1024],
            
            // Validation for the PATH string
            [['site_logo'], 'string', 'max' => 255],
            [['country'], 'safe'],
        ];
    }
    
    public function upload()
    {
        // 1. Get the file instance from the NEW attribute
        $this->logo_file = UploadedFile::getInstance($this, 'logo_file');
        
        // 2. If no file uploaded, keep the old path (site_logo already has the old value from __construct)
        if (!$this->logo_file) {
            return true; 
        }

        $basePath = Yii::getAlias('@webroot') . '/' . self::UPLOAD_BASE_DIR;

        if (!is_dir($basePath)) {
            if (!mkdir($basePath, 0777, true)) {
                $this->addError('logo_file', "Failed to create upload directory.");
                return false;
            }
        }
        
        $filename = self::UPLOAD_BASE_DIR . time() . '_' . Yii::$app->security->generateRandomString(8) . '.' . $this->logo_file->extension;
        $fullPath = Yii::getAlias('@webroot') . '/' . $filename;

        if ($this->logo_file->saveAs($fullPath)) {
            // Delete old file
            $oldPath = Yii::$app->config->get('site_logo');
            if ($oldPath && file_exists(Yii::getAlias('@webroot') . '/' . $oldPath)) {
                @unlink(Yii::getAlias('@webroot') . '/' . $oldPath);
            }
            
            // Update the string path attribute
            $this->site_logo = $filename;
            return true;
        } else {
            $this->addError('logo_file', "File upload failed.");
            return false;
        }
    }

    public function createKeys()
    {
        return Yii::$app->config->add(
            [
                ['key' => 'organization_name',        'default' => Yii::$app->name, 'category' => self::CATEGORY, 'disposition' => 0, 'label' => 'Company Name'],
                ['key' => 'organization_initials',    'default' => Yii::$app->id, 'category' => self::CATEGORY, 'disposition' => 1, 'label' => 'Initials (Reports)'],
                ['key' => 'physical_address',         'default' => 'Mombasa, Kenya', 'category' => self::CATEGORY, 'disposition' => 2, 'label' => 'Physical Address'],
                ['key' => 'email_address',            'default' => 'info@depotsystem.com', 'category' => self::CATEGORY, 'disposition' => 3, 'label' => 'Company Email'],
                ['key' => 'primary_mobile_number',    'default' => '0700000000', 'category' => self::CATEGORY, 'disposition' => 4, 'label' => 'Primary Phone'],
                ['key' => 'country',                  'default' => 'Kenya', 'category' => self::CATEGORY, 'disposition' => 5, 'label' => 'Country'],
                ['key' => 'website',                  'default' => 'https://depotsystem.com', 'category' => self::CATEGORY, 'disposition' => 6, 'label' => 'Website URL'],
                ['key' => 'site_logo',                'default' => '', 'category' => self::CATEGORY, 'disposition' => 7, 'label' => 'Company Logo (Reports)', 'input_type' => 'file'],
            ]
        );
    }

    public function attributeLabels()
    {
        return [
            'organization_name'       => 'Company Name',
            'organization_initials'   => 'Initials (for reports)',
            'physical_address'        => 'Physical Address',
            'country'                 => 'Country',
            'email_address'           => 'Company Email',
            'website'                 => 'Website URL',
            'primary_mobile_number'   => 'Primary Phone',
            'logo_file'               => 'Upload Logo',
            'site_logo'               => 'Current Logo Path',
        ];
    }

    public static function layout(): array
    {
        return [
            'organization_name'       => 'col-lg-6 col-12',
            'organization_initials'       => 'col-lg-6 col-12',
            'physical_address'        => 'col-lg-6 col-12',
            'primary_mobile_number'   => 'col-lg-6 col-12',
            'email_address'           => 'col-lg-6 col-12',
            'country'                 => 'col-lg-6 col-12',
            'website'                 => 'col-lg-12 col-12',
            'logo_file'               => 'col-lg-12 col-12', // Use the file attribute here
        ];
    }
}