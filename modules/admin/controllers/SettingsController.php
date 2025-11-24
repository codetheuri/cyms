<?php

namespace admin\controllers;

use Yii;
use yii\web\Response;
use yii\web\UploadedFile;
use admin\models\static\General; 
use admin\models\static\Tariff;

class SettingsController extends \helpers\DashboardController
{
    public $layout = 'dashboard';

    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/admin/settings');
    }
    
 public function actionGeneralSetting()
    {
        $this->layout = '@ui/views/layouts/sublayout/settings';
        $model = new General();

        if ($model->load(Yii::$app->request->post())) {
            
            // 1. Validate inputs (including file type/size)
            if ($model->validate()) {
                
                // 2. Handle Upload (This updates $model->site_logo if file exists)
                if ($model->upload()) {
                    
                    // 3. Save attributes to Config
                    foreach ($model->attributes as $key => $value) {
                        // SKIP the temporary file object
                        if ($key === 'logo_file') {
                            continue; 
                        }
                        Yii::$app->config->set($key, $value);
                    }
                    
                    Yii::$app->session->setFlash('success', 'General Settings updated successfully.');
                    return $this->refresh();
                } else {
                     Yii::$app->session->setFlash('error', 'File upload failed.');
                }
            } else {
                 Yii::$app->session->setFlash('error', 'Validation failed.');
            }
        }

        return $this->render('general_settings', ['model' => $model]);
    }

    public function actionTariffSetting()
    {
        $this->layout = '@ui/views/layouts/sublayout/settings';
        $model = new Tariff();

        if ($model->load(Yii::$app->request->post())) {
            
            if ($model->validate()) {
                // Save settings to DB Config
                foreach ($model->attributes as $key => $value) {
                    Yii::$app->config->set($key, $value);
                }
                
                Yii::$app->session->setFlash('success', 'Tariff Settings updated successfully.');
                return $this->refresh();
            } else {
                 Yii::$app->session->setFlash('error', 'Validation failed. Please check the numbers.');
            }
        }

        return $this->render('tariff_settings', ['model' => $model]);
    }

}