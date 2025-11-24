<?php

namespace dashboard\controllers;

use Yii;

use auth\models\Assignment;
use auth\models\static\Register;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use auth\models\searches\UserSearch;

/**
 * ProfileController implements the CRUD actions for User model.
 */
class ProfileController extends DashboardController
{
    public $permissions = [
        'dashboard-profile-list'=>'View User List',
        'dashboard-profile-create'=>'Add User',
        'dashboard-profile-update'=>'Edit User',
        'dashboard-profile-delete'=>'Delete User',
        'dashboard-profile-restore'=>'Restore User',
        'dashboard-profile-assignment'=>'Assign Roles and Permissions',
        ];

    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/iam/profiles');
    }
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-profile-list');
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-profile-create');
        $model = new Register();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if (($model = $model->save())) {
                        Yii::$app->session->setFlash('success', 'Account created successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_registration-form', [
                'model' => $model,
            ]);
        } else {
            return $this->redirect(['index']);
        }
    }
    public function actionUpdate($user_id)
    {
        Yii::$app->user->can('dashboard-profile-update');
        $model = $this->findModel($user_id);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'User updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_registration-form', [
                'model' => $model,
            ]);
        } else {
            return $this->redirect(['index']);
        }
    }
    public function actionTrash($user_id)
    {
        $model = $this->findModel($user_id);
        if ($model->is_deleted) {
            Yii::$app->user->can('dashboard-profile-restore');
            $model->restore();
            Yii::$app->session->setFlash('success', 'User has been restored');
        } else {
            Yii::$app->user->can('dashboard-profile-delete');
            $model->delete();
            Yii::$app->session->setFlash('success', 'User has been deleted');
        }
        return $this->redirect(['index']);
    }
    public function actionAssignment($id)
    {
        Yii::$app->user->can('dashboard-profile-assignment');
        $model = $this->findAssignmentModel($id);
        return $this->renderAjax('_manage_access', [
                'model' => $model,
        ]);
    }
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = new Assignment($id);
        $success = $model->assign($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems(), ['success' => $success]);
    }
    public function actionRevoke($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = new Assignment($id);
        $success = $model->revoke($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems(), ['success' => $success]);
    }
    protected function findAssignmentModel($id)
    {
        $class = Yii::$app->getUser()->identityClass;
        if (($user = $class::findIdentity($id)) !== null) {
            return new Assignment($id, $user);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    protected function findModel($user_id)
    {
        $class = Yii::$app->getUser()->identityClass;
        if (($model = $class::findIdentity($user_id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
