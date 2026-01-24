<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\BillingRecords;
use dashboard\models\search\BillingRecordsSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use dashboard\models\BillingPayments;
use dashboard\models\ContainerVisits;

class BillingController extends DashboardController
{
    public $permissions = [
        'dashboard-billing-list' => 'View BillingRecords List',
        'dashboard-billing-view' => 'View BillingRecords Details',
        'dashboard-billing-create' => 'Add BillingRecords',
        'dashboard-billing-update' => 'Edit BillingRecords',
        'dashboard-billing-delete' => 'Delete BillingRecords',
        'dashboard-billing-restore' => 'Restore BillingRecords',
    ];

    public function getViewPath()
    {
        return Yii::getAlias('@ui/views/cyms/billing');
    }
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-billing-list');
        $searchModel = new BillingRecordsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        // Recalculate balances for all displayed records
        foreach ($dataProvider->getModels() as $model) {
            $model->recalculateBalance();
        }
        //  $model->recalculateBalance();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        Yii::$app->user->can('dashboard-billing-create');
        $model = new BillingRecords();

        if ($model->load(Yii::$app->request->post())) {

            $model->tariff_rate = Yii::$app->config->get('storage_rate_per_day') ?? 0;
            $liftOn = Yii::$app->config->get('lift_on_charges') ?? 0;
            $liftOff = Yii::$app->config->get('lift_off_charges') ?? 0;
            $model->lift_charges = $liftOn + $liftOff;

            $visit = ContainerVisits::findOne($model->visit_id);
            if ($visit) {
                $startStr = $visit->date_in . ' ' . ($visit->time_in ?: '00:00:00');
                $diffSeconds = time() - strtotime($startStr);

                // New Logic
                $model->storage_days = ($diffSeconds < 0) ? 1 : (floor($diffSeconds / 86400) + 1);
            }

            if ($model->save() && $model->recalculateBalance()) {
                Yii::$app->session->setFlash('success', 'Invoice generated successfully');
                return $this->redirect(['view', 'id' => $model->bill_id]);
            }
        }
    }
    public function actionUpdate($bill_id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($bill_id);

        if ($this->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()) {
                    if ($model->save()) {
                        Yii::$app->session->setFlash('success', 'BillingRecords updated successfully');
                        return $this->redirect(['index']);
                    }
                }
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    public function actionView($id)

    {
        Yii::$app->user->can('dashboard-billing-view');
        $model = $this->findModel($id);
        $visit = $model->visit;
        if ($visit->status !== 'GATE_OUT') {

            $model->recalculateBalance();
        }

        $paymentModel = new BillingPayments();
        $paymentModel->bill_id = $id;
        $paymentModel->transaction_date = date('Y-m-d');

        return $this->render('view', [
            'model' => $model,
            'paymentModel' => $paymentModel,
        ]);
    }

    public function actionPayment($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $payment = new BillingPayments();
        $payment->bill_id = $id;


        if ($payment->load(Yii::$app->request->post())) {
            if ($payment->save()) {
                Yii::$app->session->setFlash('success', 'Payment Recorded Successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to record payment.');
            }
        }
        return $this->redirect(['view', 'id' => $id]);
    }
    // public function actionTrash($bill_id)
    // {
    //     $model = $this->findModel($bill_id);
    //     if ($model->is_deleted) {
    //         Yii::$app->user->can('dashboard-billing-restore');
    //         $model->restore();
    //         Yii::$app->session->setFlash('success', 'BillingRecords has been restored');
    //     } else {
    //         Yii::$app->user->can('dashboard-billing-delete');
    //         $model->delete();
    //         Yii::$app->session->setFlash('success', 'BillingRecords has been deleted');
    //     }
    //     return $this->redirect(['index']);
    // }
   // dashboard/controllers/BillingController.php

public function actionAuthorizeCredit($id)
{
    Yii::$app->user->can('dashboard-billing-update');
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post())) {
        
        // 1. Upload File
        if ($model->uploadAgreement()) {
            
            $model->status = 'CREDIT';

            // 2. Validate & Save (This will now include atl_number)
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Credit Authorized with ATL #' . $model->atl_number);
                return $this->redirect(['view', 'id' => $id]);
            } else {
                 Yii::$app->session->setFlash('error', 'Validation failed: ' . json_encode($model->errors));
            }
        } else {
            Yii::$app->session->setFlash('error', 'Failed to upload agreement document.');
        }
    }
    return $this->redirect(['view', 'id' => $id]);
}
    public function actionUpdateDiscount($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // Just save and recalculate. The logic handles the subtraction.
            if ($model->recalculateBalance()) {
                Yii::$app->session->setFlash('success', 'Discount updated successfully.');
            }
        }
        return $this->redirect(['view', 'id' => $id]);
    }
    public function actionUpdateRate($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        // Only update rate, then recalculate everything else
        if ($model->load(Yii::$app->request->post())) {
            if ($model->recalculateBalance()) {
                Yii::$app->session->setFlash('success', 'Daily Rate updated successfully.');
            }
        }
        return $this->redirect(['view', 'id' => $id]);
    }
    // In BillingController.php

    public function actionToggleLiftOn($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        $cost = (float) Yii::$app->config->get('lift_on_charges');

        // Check if Lift On is already "inside" the total
        // Logic: If current charges seem high enough to include Lift On, remove it. 
        // Since we don't have separate columns, we use a session flag or simple math assumption.
        // BETTER APPROACH for "No Migration": Just Add/Subtract explicitly.

        $action = Yii::$app->request->post('action'); // 'add' or 'remove'

        if ($action === 'add') {
            $model->lift_charges += $cost;
            Yii::$app->session->setFlash('success', 'Lift On Charge Added.');
        } else {
            $model->lift_charges -= $cost;
            if ($model->lift_charges < 0) $model->lift_charges = 0;
            Yii::$app->session->setFlash('warning', 'Lift On Charge Removed.');
        }

        $model->recalculateBalance();
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionToggleLiftOff($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);
        $cost = (float) Yii::$app->config->get('lift_off_charges');

        $action = Yii::$app->request->post('action'); // 'add' or 'remove'

        if ($action === 'add') {
            $model->lift_charges += $cost;
            Yii::$app->session->setFlash('success', 'Lift Off Charge Added.');
        } else {
            $model->lift_charges -= $cost;
            if ($model->lift_charges < 0) $model->lift_charges = 0;
            Yii::$app->session->setFlash('warning', 'Lift Off Charge Removed.');
        }

        $model->recalculateBalance();
        return $this->redirect(['view', 'id' => $id]);
    }
    public function actionUpdateCreditDetails($id)
    {
        Yii::$app->user->can('dashboard-billing-update'); // Ensure permission
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // We use save(false) to skip strictly validating the whole model 
            // (like file uploads) since we are just fixing text typos.
            // But we specifically only update these two attributes.
            $model->updateAttributes([
                'atl_number' => $model->atl_number,
                'authorized_by' => $model->authorized_by
            ]);
            
            Yii::$app->session->setFlash('success', 'Credit Authorization details updated.');
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }
    protected function findModel($bill_id)
    {
        if (($model = BillingRecords::findOne(['bill_id' => $bill_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
