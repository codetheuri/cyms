<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\MasterRepairCodes;
use dashboard\models\search\MasterRepairCodesSearch; // You'll need to generate this search model via Gii
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class RepairCodeController extends DashboardController
{
    public $layout = 'dashboard';

    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/repair_codes');
    }
    public $permissions = [
        'dashboard-repair-code-list' => 'View Repair Code List',
        'dashboard-repair-code-create' => 'Add Repair Code',
        'dashboard-repair-code-update' => 'Edit Repair Code',
        'dashboard-repair-code-delete' => 'Delete Repair Code',
    ];

    /**
     * List all Repair Codes
     */
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-repair-code-list');
        $dataProvider = new ActiveDataProvider([
            'query' => MasterRepairCodes::find(),
            'sort' => ['defaultOrder' => ['repair_code' => SORT_ASC]],
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Create a new Repair Code
     */
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-repair-code-create');
        $model = new MasterRepairCodes();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Repair Code Added Successfully');
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Update existing Repair Code
     */
    public function actionUpdate($id)

    {
        Yii::$app->user->can('dashboard-repair-code-update');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Repair Code Updated');
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Delete Code
     */
    public function actionDelete($id)
    {
        Yii::$app->user->can('dashboard-repair-code-delete');
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * AJAX Quick Add (For Survey Form Modal)
     */
    public function actionAjaxCreate()

    {
        Yii::$app->user->can('dashboard-repair-code-create');
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new MasterRepairCodes();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return [
                'success' => true, 
                'id' => $model->repair_code, // Use code as value
                'name' => $model->repair_code . ' - ' . $model->description,
                // Return data for auto-fill
                'data' => [
                    'desc' => $model->description,
                    'hours' => $model->standard_hours,
                    'mat' => $model->material_cost,
                    'lab' => $model->labor_cost
                ]
            ];
        }
        return ['success' => false, 'errors' => $model->errors];
    }

    protected function findModel($id)
    {
        if (($model = MasterRepairCodes::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}