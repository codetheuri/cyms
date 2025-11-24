<?php

namespace dashboard\controllers;

use Yii;
use auth\models\static\Login;
use auth\models\static\ChangePassword;

class IamController extends \helpers\DashboardController
{
    public function actionLogin()
    {
        $this->layout = 'auth';
        $model = new Login();
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionChangePassword()
    {
        $user = Yii::$app->user->identity; 
        $model = new ChangePassword($user);

        if ($model->load(Yii::$app->request->post())) {
            
            if ($model->validate() && $model->changePassword()) {
                Yii::$app->session->setFlash('success', 'Password changed successfully. Please login again.');
                
                Yii::$app->user->logout();
                return $this->redirect(['login']);
            }
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => false, 'errors' => $model->getErrors()];
            }
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('change-password', [
                'model' => $model,
            ]);
        } else {
            return $this->redirect(['/dashboard']);
        }
    }
}