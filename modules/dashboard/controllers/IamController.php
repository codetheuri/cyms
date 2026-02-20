<?php

namespace dashboard\controllers;

use Yii;
use auth\models\static\Login;
use yii\base\DynamicModel;
use auth\models\User;
use auth\models\static\ChangePassword;

class IamController extends \helpers\DashboardController
{
    // public function getViewPath()
    // {
    //     return Yii::getAlias('@ui/views/iam');
    // }

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

    // public function actionChangePassword()
    // {
    //      Yii::$app->user->can('dashboard-profile-update');
    //     $user = Yii::$app->user->identity; 
    //     $model = new ChangePassword($user);

    //     if ($model->load(Yii::$app->request->post())) {

    //         if ($model->validate() && $model->changePassword()) {
    //             Yii::$app->session->setFlash('success', 'Password changed successfully. Please login again.');

    //             Yii::$app->user->logout();
    //             return $this->redirect(['login']);
    //         }
    //         if (Yii::$app->request->isAjax) {
    //             Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    //             return ['success' => false, 'errors' => $model->getErrors()];
    //         }
    //     }
    //     if (Yii::$app->request->isAjax) {
    //         return $this->renderAjax('change-password', [
    //             'model' => $model,
    //         ]);
    //     } else {
    //         return $this->redirect(['/dashboard']);
    //     }
    // }
  public function actionResetPassword($id)
    {
        // Enforce Admin Permission
        Yii::$app->user->can('dashboard-profile-update');

        $user = User::findOne($id);
        if (!$user) {
            throw new \yii\web\NotFoundHttpException('User not found.');
        }

        $model = new DynamicModel(['new_password', 'confirm_password']);
        $model->addRule(['new_password', 'confirm_password'], 'required')
            ->addRule(['new_password'], 'string', ['min' => 6])
            ->addRule('confirm_password', 'compare', ['compareAttribute' => 'new_password', 'message' => 'Passwords do not match.']);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user->setPassword($model->new_password);
            $user->generateAuthKey(); // Logs out active sessions for this user

            if ($user->save(false)) {
                Yii::$app->session->setFlash('success', "Password for {$user->username} has been successfully reset.");
                return $this->redirect(Yii::$app->request->referrer ?: ['/dashboard']);
            }
        }

        if (Yii::$app->request->isAjax) {
            // NOTE: Renders a different view file to avoid conflicts
            return $this->renderAjax('reset-password', [
                'model' => $model,
                'user' => $user,
            ]);
        }
        return $this->redirect(['/dashboard']);
    }
   public function actionChangePassword()
    {
        if (!Yii::$app->user->can('dashboard-profile-update')) {
            if (Yii::$app->request->isAjax) {
                return '<div class="alert alert-danger mb-0">Users are not permitted to change their own passwords. Please contact an Administrator.</div>';
            }
            Yii::$app->session->setFlash('error', 'Users are not permitted to change their own passwords.');
            return $this->redirect(['/dashboard']);
        }

        $user = Yii::$app->user->identity; 
        $model = new ChangePassword($user);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate() && $model->changePassword()) {
                Yii::$app->session->setFlash('success', 'Your password was changed successfully. Please login again.');
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
