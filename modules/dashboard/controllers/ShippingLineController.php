<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\MasterShippingLines;
use dashboard\models\search\MasterShippingLinesSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;

/**
 * ShippingLineController implements the CRUD actions for MasterShippingLines model.
 */
class ShippingLineController extends DashboardController
{
    public $permissions = [
        'dashboard-shipping-line-list'=>'View MasterShippingLines List',
        'dashboard-shipping-line-create'=>'Add MasterShippingLines',
        'dashboard-shipping-line-update'=>'Edit MasterShippingLines',
        'dashboard-shipping-line-delete'=>'Delete MasterShippingLines',
        'dashboard-shipping-line-restore'=>'Restore MasterShippingLines',
        ];
          public function getViewPath() {
        return Yii::getAlias('@ui/views/cyms/shipping_lines');
    }  
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-shipping-line-list');
        $searchModel = new MasterShippingLinesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-shipping-line-create');
        $model = new MasterShippingLines();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'MasterShippingLines created successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }
       if ($this->request->isAjax) {
            return $this->renderAjax('create', ['model' => $model]);
        } else {
            return $this->render('create', ['model' => $model]);
        }
    }
    public function actionUpdate($line_id)
    {
        Yii::$app->user->can('dashboard-shipping-line-update');
        $model = $this->findModel($line_id);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'MasterShippingLines updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
         if ($this->request->isAjax) {
            return $this->renderAjax('update', ['model' => $model]);
        } else {
            return $this->render('update', ['model' => $model]);
        }
    
      
    }
    public function actionTrash($line_id)
    {
        $model = $this->findModel($line_id);
        if ($model->is_deleted) {
            Yii::$app->user->can('dashboard-shipping-line-restore');
            $model->restore();
            Yii::$app->session->setFlash('success', 'MasterShippingLines has been restored');
        } else {
            Yii::$app->user->can('dashboard-shipping-line-delete');
            $model->delete();
            Yii::$app->session->setFlash('success', 'MasterShippingLines has been deleted');
        }
        return $this->redirect(['index']);
    }
    protected function findModel($line_id)
    {
        if (($model = MasterShippingLines::findOne(['line_id' => $line_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    public function actionAjaxCreate()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
    $model = new \dashboard\models\MasterShippingLines();
    
    if ($model->load(Yii::$app->request->post())) {
        // Auto-generate timestamp if behaviors aren't set
        $model->created_at = time();
        $model->updated_at = time();

        if ($model->save()) {
            return [
                'success' => true,
                'id' => $model->line_id,
                'name' => $model->line_name,
                'message' => 'Shipping Line Added!'
            ];
        }
        
        return ['success' => false, 'errors' => $model->errors];
    }
    
    return ['success' => false, 'message' => 'No data received'];
}
}
