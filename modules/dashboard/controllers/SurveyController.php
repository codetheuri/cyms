<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\ContainerSurveys;
use dashboard\models\search\ContainerSurveysSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;

/**
 * SurveyController implements the CRUD actions for ContainerSurveys model.
 */
class SurveyController extends DashboardController
{
    public $permissions = [
        'dashboard-survey-list'=>'View ContainerSurveys List',
        'dashboard-survey-create'=>'Add ContainerSurveys',
        'dashboard-survey-update'=>'Edit ContainerSurveys',
        'dashboard-survey-delete'=>'Delete ContainerSurveys',
        'dashboard-survey-restore'=>'Restore ContainerSurveys',
        ];
          public function getViewPath() {
        return Yii::getAlias('@ui/views/cyms/surveys');
    }  
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-survey-list');
        $searchModel = new ContainerSurveysSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    public function actionView($survey_id)
    {
        
        Yii::$app->user->can('dashboard-survey-list'); 
        return $this->render('view', [
            'model' => $this->findModel($survey_id),
        ]);
    }
    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-survey-create');
        $model = new ContainerSurveys();
        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'ContainerSurveys created successfully');
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
    public function actionUpdate($survey_id)
    {
        Yii::$app->user->can('dashboard-survey-update');
        $model = $this->findModel($survey_id);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'ContainerSurveys updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionTrash($survey_id)
    {
        $model = $this->findModel($survey_id);
        if ($model->is_deleted) {
            Yii::$app->user->can('dashboard-survey-restore');
            $model->restore();
            Yii::$app->session->setFlash('success', 'ContainerSurveys has been restored');
        } else {
            Yii::$app->user->can('dashboard-survey-delete');
            $model->delete();
            Yii::$app->session->setFlash('success', 'ContainerSurveys has been deleted');
        }
        return $this->redirect(['index']);
    }
    protected function findModel($survey_id)
    {
        if (($model = ContainerSurveys::findOne(['survey_id' => $survey_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
