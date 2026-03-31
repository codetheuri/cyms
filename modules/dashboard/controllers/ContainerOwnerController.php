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
    public $permissions = [
        'dashboard-container-owner-list' => 'View Container Owner List',
        'dashboard-container-owner-create' => 'Add Container Owner',
        'dashboard-container-owner-update' => 'Edit Container Owner',
        'dashboard-container-owner-delete' => 'Delete Container Owner',
        'dashboard-container-owner-restore' => 'Restore Container Owner',
        'dashboard-container-owner-view' => 'View Container Owner Details',
    ];

    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-container-owner-list');
        $searchModel = new MasterContainerOwnersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

  
   public function actionView($id)
    {
        Yii::$app->user->can('dashboard-container-owner-view');
        $model = $this->findModel($id);

        // 1. IN YARD CONTAINERS
        $queryInYard = ContainerVisits::find()
            ->where(['container_owner_id' => $id])
            ->andWhere(['status' => ['IN_YARD', 'SURVEYED']]);
            
        $dataProviderInYard = new \yii\data\ActiveDataProvider([
            'query' => $queryInYard,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['date_in' => SORT_DESC]],
        ]);

        // 2. UNPAID BILLS (Fix: Use andWhere)
        $queryUnpaid = BillingRecords::find()
            ->joinWith(['visit']) // Join to check owner
            ->where(['container_visits.container_owner_id' => $id]) // Filter by THIS Client
            ->andWhere(['>', 'billing_records.balance', 0.01])      // AND check balance
            ->andWhere(['billing_records.status' => ['UNPAID', 'PARTIAL', 'CREDIT']]); // Optional: Be specific

        $dataProviderUnpaid = new \yii\data\ActiveDataProvider([
            'query' => $queryUnpaid,
            'pagination' => ['pageSize' => 10],
        ]);

        // 3. HISTORY (Fix: Use andWhere)
        $queryHistory = BillingRecords::find()
            ->joinWith(['visit'])
            ->where(['container_visits.container_owner_id' => $id]) // Filter by THIS Client
            ->andWhere(['billing_records.status' => ['PAID', 'CREDIT']])
            ->orderBy(['updated_at' => SORT_DESC]);

        $dataProviderHistory = new \yii\data\ActiveDataProvider([
            'query' => $queryHistory,
            'pagination' => ['pageSize' => 10],
        ]);
        
        // 4. CALCULATE TOTALS (Using the corrected queries)
        // Note: sum() executes a DB query, so we clone to be safe, though not strictly required here
        $totalDue = $queryUnpaid->sum('billing_records.balance') ?? 0;
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
        Yii::$app->user->can('dashboard-container-owner-create');
        $model = new MasterContainerOwners();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Client added successfully');
            return $this->redirect(['view', 'id' => $model->owner_id]);
        }
        return $this->renderAjax('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        Yii::$app->user->can('dashboard-container-owner-update');
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Client updated successfully');
            return $this->redirect(['view', 'id' => $model->owner_id]);
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionExport($id, $type = 'print')
    {
        Yii::$app->user->can('dashboard-container-owner-view');
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
    public function actionAddRate($id)
    {
        $model = new \dashboard\models\ClientRates();
        $model->owner_id = $id;
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Rate added successfully.');
        } else {
            Yii::$app->session->setFlash('error', 'Could not save rate. It might already exist.');
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionDeleteRate($id)
    {
        $rate = \dashboard\models\ClientRates::findOne($id);
        if ($rate) {
            $ownerId = $rate->owner_id;
            $rate->delete();
            Yii::$app->session->setFlash('success', 'Rate removed.');
            return $this->redirect(['view', 'id' => $ownerId]);
        }
        return $this->redirect(['index']);
    }
 public function actionChangeCurrency($id)
    {
        $model = $this->findModel($id); 
        
        $oldCurrency = $model->billing_currency ?: 'KES';
        $newCurrency = Yii::$app->request->post('billing_currency');
        
        if ($newCurrency && in_array($newCurrency, ['KES', 'USD']) && $oldCurrency !== $newCurrency) {
            
            $exRate = class_exists('\dashboard\hooks\Currency') 
                ? \dashboard\hooks\Currency::getUsdToKesRate() 
                : (float) Yii::$app->config->get('fallback_exchange_rate', 130.00);
            
            if ($exRate <= 0) $exRate = 130.00;

            $transaction = Yii::$app->db->beginTransaction();
            
            try {
                // 1. Update Profile
                $model->billing_currency = $newCurrency;
                if (!$model->save(false)) throw new \Exception('Failed to update client.');

                // 2. Convert Custom Rates
                $customRates = \dashboard\models\ClientRates::findAll(['owner_id' => $id]);
                foreach ($customRates as $rate) {
                    if ($newCurrency === 'USD' && $oldCurrency === 'KES') {
                        $rate->daily_rate = round($rate->daily_rate / $exRate, 2);
                    } elseif ($newCurrency === 'KES' && $oldCurrency === 'USD') {
                        $rate->daily_rate = round($rate->daily_rate * $exRate, 2);
                    }
                    $rate->save(false);
                }

                // 3. CONVERT UNPAID BILLS (Including Discounts)
                $visits = \dashboard\models\ContainerVisits::find()->select('visit_id')->where(['container_owner_id' => $id])->column();
                
                if (!empty($visits)) {
                    $unpaidBills = \dashboard\models\BillingRecords::find()
                        ->where(['visit_id' => $visits])
                        ->andWhere(['in', 'status', ['UNPAID', 'PARTIAL']])
                        ->all();
                    
                    foreach ($unpaidBills as $bill) {
                        
                        // Convert the Discount Amount mathematically
                        if ($newCurrency === 'USD' && $bill->currency === 'KES') {
                            $bill->discount_amount = round($bill->discount_amount / $exRate, 2);
                        } elseif ($newCurrency === 'KES' && $bill->currency === 'USD') {
                            $bill->discount_amount = round($bill->discount_amount * $exRate, 2);
                        }
                        
                        // Force Recalculation from KES Master
                        $bill->tariff_rate = 0; 
                        $bill->lift_charges = 0; 
                        $bill->currency = $newCurrency;
                        
                        $bill->save(false);
                        $bill->recalculateBalance(); 
                    }
                }
                
                $transaction->commit();
                Yii::$app->session->setFlash('success', "Client currency updated. All unpaid bills, rates, and discounts converted mathematically.");

            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error updating currency: ' . $e->getMessage());
            }
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }
    protected function findModel($id)
    {
        if (($model = MasterContainerOwners::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Client not found.');
    }
}