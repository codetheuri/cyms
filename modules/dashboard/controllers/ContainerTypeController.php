<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\MasterContainerTypes;
use dashboard\models\search\MasterContainerTypesSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;

/**
 * ContainerTypeController implements the CRUD actions for MasterContainerTypes model.
 */
class ContainerTypeController extends DashboardController
{
    public $permissions = [
        'dashboard-container-type-list' => 'View MasterContainerTypes List',
        'dashboard-container-type-create' => 'Add MasterContainerTypes',
        'dashboard-container-type-update' => 'Edit MasterContainerTypes',
        'dashboard-container-type-delete' => 'Delete MasterContainerTypes',
        'dashboard-container-type-restore' => 'Restore MasterContainerTypes',
    ];
    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/container_types');
    }
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-container-type-list');
        $searchModel = new MasterContainerTypesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-container-type-create');
        $model = new MasterContainerTypes();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'MasterContainerTypes created successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function actionUpdate($type_id)
    {
        Yii::$app->user->can('dashboard-container-type-update');
        $model = $this->findModel($type_id);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'MasterContainerTypes updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    // public function actionTrash($type_id)
    // {
    //     $model = $this->findModel($type_id);
    //     if ($model->is_deleted) {
    //         Yii::$app->user->can('dashboard-container-type-restore');
    //         $model->restore();
    //         Yii::$app->session->setFlash('success', 'MasterContainerTypes has been restored');
    //     } else {
    //         Yii::$app->user->can('dashboard-container-type-delete');
    //         $model->delete();
    //         Yii::$app->session->setFlash('success', 'MasterContainerTypes has been deleted');
    //     }
    //     return $this->redirect(['index']);
    // }
    protected function findModel($type_id)
    {
        if (($model = MasterContainerTypes::findOne(['type_id' => $type_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionAjaxCreate()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $model = new \dashboard\models\MasterContainerTypes();
    
    if ($model->load(Yii::$app->request->post()) && $model->save()) {
        // Format the display name: "40' HC (45G1)"
        $displayName = $model->size . "' " . $model->type_group . ' (' . $model->iso_code . ')';
        return ['success' => true, 'id' => $model->type_id, 'name' => $displayName];
    }
    
    return ['success' => false, 'errors' => $model->errors];
}
}
