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
                $in = new \DateTime($visit->date_in);
                $now = new \DateTime();
                $days = $in->diff($now)->days;
                $model->storage_days = ($days < 1) ? 1 : $days;
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
        $model = $this->findModel($id);
        $visit = $model->visit;
        if ($visit->status !== 'GATE_OUT') {
            $in = new \DateTime($visit->date_in);
            $now = new \DateTime();
            $days = $in->diff($now)->days;
            if ($days < 1) $days = 1;
            if ($model->storage_days != $days) {
                $model->storage_days = $days;
                $model->recalculateBalance();
            }
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
    public function actionAuthorizeCredit($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

         
            if ($model->uploadAgreement()) {
               
                $model->status = 'CREDIT';

                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Credit Authorized. Container can now be released.');
                    return $this->redirect(['view', 'id' => $id]);
                }
            } else {
                Yii::$app->session->setFlash('error', 'Failed to upload agreement document.');
            }
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
