<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\YardSlots;
use dashboard\models\search\YardSlotsSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;

/**
 * YardController implements the CRUD actions for YardSlots model.
 */
class YardController extends DashboardController
{
    public $permissions = [
        'dashboard-yard-list'=>'View YardSlots List',
        'dashboard-yard-create'=>'Add YardSlots',
        'dashboard-yard-update'=>'Edit YardSlots',
        'dashboard-yard-delete'=>'Delete YardSlots',
        'dashboard-yard-restore'=>'Restore YardSlots',
        ];
        public function getViewPath() {
        return Yii::getAlias('@ui/views/cyms/yard');
    }
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-yard-list');
        $searchModel = new YardSlotsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-yard-create');
        $model = new YardSlots();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'YardSlots created successfully');
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
    public function actionUpdate($slot_id)
    {
        Yii::$app->user->can('dashboard-yard-update');
        $model = $this->findModel($slot_id);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'YardSlots updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionTrash($slot_id)
    {
        $model = $this->findModel($slot_id);
        if ($model->is_deleted) {
            Yii::$app->user->can('dashboard-yard-restore');
            $model->restore();
            Yii::$app->session->setFlash('success', 'YardSlots has been restored');
        } else {
            Yii::$app->user->can('dashboard-yard-delete');
            $model->delete();
            Yii::$app->session->setFlash('success', 'YardSlots has been deleted');
        }
        return $this->redirect(['index']);
    }
    protected function findModel($slot_id)
    {
        if (($model = YardSlots::findOne(['slot_id' => $slot_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
