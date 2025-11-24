<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\MasterContainerOwners;
use dashboard\models\search\MasterContainerOwnersSearch;
use dashboard\models\ContainerVisits;
use dashboard\models\BillingRecords;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use admin\models\static\General;
class ContainerOwnerController extends DashboardController
{
    public $layout = 'dashboard';
    
    public function getViewPath() {
        return Yii::getAlias('@ui/views/cyms/clients');
    }

    public function actionIndex()
    {
        $searchModel = new MasterContainerOwnersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

  
    public function actionView($id)
    {
        $model = $this->findModel($id);

      
        $queryInYard = ContainerVisits::find()
            ->where(['container_owner_id' => $id])
            ->andWhere(['status' => ['IN_YARD', 'SURVEYED']]);
            
        $dataProviderInYard = new ActiveDataProvider([
            'query' => $queryInYard,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['date_in' => SORT_DESC]],
        ]);

      
        $queryUnpaid = BillingRecords::find()
            ->joinWith(['visit'])
            ->where(['container_visits.container_owner_id' => $id])
           ->where(['>', 'billing_records.balance', 0]);
            // ->andWhere(['billing_records.status' => ['UNPAID', 'PARTIAL']]);

        $dataProviderUnpaid = new ActiveDataProvider([
            'query' => $queryUnpaid,
            'pagination' => ['pageSize' => 10],
        ]);

     
        $queryHistory = BillingRecords::find()
            ->joinWith(['visit'])
            ->where(['container_visits.container_owner_id' => $id])
            ->andWhere(['billing_records.status' => ['PAID', 'CREDIT']])
            ->orderBy(['updated_at' => SORT_DESC]);

        $dataProviderHistory = new ActiveDataProvider([
            'query' => $queryHistory,
            'pagination' => ['pageSize' => 10],
        ]);
        
       
        $totalDue = $queryUnpaid->sum('balance') ?? 0;
        $totalContainers = $queryInYard->count();

        return $this->render('view', [
            'model' => $model,
            'dataProviderInYard' => $dataProviderInYard,
            'dataProviderUnpaid' => $dataProviderUnpaid,
            'dataProviderHistory' => $dataProviderHistory,
            'totalDue' => $totalDue,
            'totalContainers' => $totalContainers,
        ]);
    }

    public function actionCreate()
    {
        $model = new MasterContainerOwners();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Client added successfully');
            return $this->redirect(['view', 'id' => $model->owner_id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Client updated successfully');
            return $this->redirect(['view', 'id' => $model->owner_id]);
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionExport($id, $type = 'print')
    {
        $model = $this->findModel($id);
        $settings = new General();

        // 1. FETCH DATA
        
        // A. Containers In Yard
        $inYard = ContainerVisits::find()
            ->where(['container_owner_id' => $id])
            ->andWhere(['status' => ['IN_YARD', 'SURVEYED']])
            ->orderBy(['date_in' => SORT_ASC])
            ->all();

        // B. Unpaid Invoices
        $unpaid = BillingRecords::find()
            ->joinWith(['visit'])
            ->where(['container_visits.container_owner_id' => $id])
              ->where(['>', 'billing_records.balance', 0])
            // ->andWhere(['billing_records.status' => ['UNPAID', 'PARTIAL']])
            ->all();

        // C. Recent History (Limit to last 50 for report readability)
        $history = BillingRecords::find()
            ->joinWith(['visit'])
            ->where(['container_visits.container_owner_id' => $id])
            ->andWhere(['billing_records.status' => ['PAID', 'CREDIT']])
            ->orderBy(['updated_at' => SORT_DESC])
            ->limit(50)
            ->all();

        // 2. RENDER BASED ON TYPE

        if ($type === 'excel') {
            // Force download as Excel file (HTML Table method)
            $filename = 'Client_Statement_' . $model->owner_name . '_' . date('Ymd') . '.xls';
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            $this->layout = false;
            return $this->renderPartial('client_report', [
                'model' => $model,
                'inYard' => $inYard,
                'unpaid' => $unpaid,
                'history' => $history,
                'settings' => $settings,
                'isExcel' => true // Flag to strip images/buttons
            ]);
        } else {
            // Print/PDF View
            $this->layout = false;
            return $this->render('client_report', [
                'model' => $model,
                'inYard' => $inYard,
                'unpaid' => $unpaid,
                'history' => $history,
                'settings' => $settings,
                'isExcel' => false
            ]);
        }
    }
    protected function findModel($id)
    {
        if (($model = MasterContainerOwners::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Client not found.');
    }
}