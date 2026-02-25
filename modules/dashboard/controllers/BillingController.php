<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\BillingRecords;
use dashboard\models\search\BillingRecordsSearch;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use dashboard\models\BillingPayments;
use dashboard\models\ContainerVisits;
use kartik\mpdf\Pdf;

class BillingController extends DashboardController
{
    public $permissions = [
        'dashboard-billing-list' => 'View BillingRecords List',
        'dashboard-billing-view' => 'View BillingRecords Details',
        'dashboard-billing-create' => 'Add BillingRecords',
        'dashboard-billing-update' => 'Edit BillingRecords',
        'dashboard-billing-delete' => 'Delete BillingRecords',
        'dashboard-billing-restore' => 'Restore BillingRecords',
        // 'approve-credit' => 'Approve Credit Requests', // Ensure this permission exists if using RBAC
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
            $visit = ContainerVisits::findOne($model->visit_id);
            if ($visit) {
                // ALWAYS save the Lift Charges strictly in KES Master Cost
                $kesLiftOn  = (float) Yii::$app->config->get('lift_on_charges');
                $kesLiftOff = (float) Yii::$app->config->get('lift_off_charges');
                $model->lift_charges = $kesLiftOn + $kesLiftOff;

                $startStr = $visit->date_in . ' ' . ($visit->time_in ?: '00:00:00');
                $diffSeconds = time() - strtotime($startStr);
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
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'BillingRecords updated successfully');
                    return $this->redirect(['index']);
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
        $clientCurrency = $visit->containerOwner ? $visit->containerOwner->billing_currency : 'KES';

        // NO MORE MATH CONVERSIONS HERE! Database stays pure KES.
        if ($model->currency !== $clientCurrency && !empty($model->currency)) {
            $model->currency = $clientCurrency;
            $model->save(false);
            Yii::$app->session->setFlash('info', 'Currency presentation updated to ' . $clientCurrency . '.');
        }

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
                // Ensure the master bill reflects the payment immediately
                $model = $this->findModel($id);
                $model->recalculateBalance();

                Yii::$app->session->setFlash('success', 'Payment Recorded Successfully.');
            } else {
                Yii::$app->session->setFlash('error', 'Failed to record payment.');
            }
        }

        $returnClientId = Yii::$app->request->get('return_client');
        return $this->redirect(['view', 'id' => $id, 'return_client' => $returnClientId]);
    }

    /**
     * CLERK: Submit Request (Fixed Debugging)
     */
    public function actionRequestCredit($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->approval_status = 'PENDING';
            $model->requested_by = Yii::$app->user->id;
            $model->requested_at = time();

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Credit request sent to Admin for approval.');
                return $this->redirect(['view', 'id' => $model->bill_id]);
            } else {
                $errors = json_encode($model->getErrors());
                Yii::$app->session->setFlash('error', 'Validation Error: ' . $errors);
            }
        }

        return $this->redirect(['view', 'id' => $model->bill_id]);
    }

    /**
     * SUPERVISOR: Authorize Credit
     */
    public function actionAuthorizeCredit($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'CREDIT';

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Credit Authorized with ATL #' . $model->atl_number);
            } else {
                Yii::$app->session->setFlash('error', 'Validation failed: ' . json_encode($model->errors));
            }
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    // ADMIN: List Pending Requests
    public function actionCreditRequests()
    {
        Yii::$app->user->can('dashboard-billing-delete');

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => \dashboard\models\BillingRecords::find()
                ->where(['approval_status' => 'PENDING'])
                ->orderBy(['requested_at' => SORT_DESC]),
        ]);

        return $this->render('credit_requests', ['dataProvider' => $dataProvider]);
    }

    // ADMIN: Approve Request
    public function actionApproveCredit($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'CREDIT';
            $model->approval_status = 'APPROVED';
            $model->authorized_by = Yii::$app->user->identity->username;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Credit Authorized successfully.');
                return $this->redirect(['credit-requests']);
            } else {
                Yii::$app->session->setFlash('error', 'Approval failed: ' . json_encode($model->errors));
            }
        }
        return $this->redirect(['credit-requests']);
    }

    // ADMIN: Reject Request
    public function actionRejectCredit($id)
    {
        $model = $this->findModel($id);
        $reason = Yii::$app->request->post('rejection_reason', 'No reason provided');

        $model->approval_status = 'REJECTED';
        $model->rejection_reason = $reason;

        if ($model->save()) {
            Yii::$app->session->setFlash('warning', 'Request Rejected.');
        } else {
            Yii::$app->session->setFlash('error', 'Rejection failed: ' . json_encode($model->errors));
        }

        return $this->redirect(['credit-requests']);
    }

    public function actionUpdateDiscount($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            // If the user typed a USD amount in the form, convert it BACK to KES before saving!
            if ($model->currency === 'USD') {
                $exRate = class_exists('\dashboard\hooks\Currency') ? \dashboard\hooks\Currency::getUsdToKesRate() : 130.00;
                $model->discount_amount = $model->discount_amount * $exRate;
            }
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

        if ($model->load(Yii::$app->request->post())) {
            // If the user typed a USD rate in the form, convert it BACK to KES before saving!
            if ($model->currency === 'USD') {
                $exRate = class_exists('\dashboard\hooks\Currency') ? \dashboard\hooks\Currency::getUsdToKesRate() : 130.00;
                $model->tariff_rate = $model->tariff_rate * $exRate;
            }
            if ($model->recalculateBalance()) {
                Yii::$app->session->setFlash('success', 'Daily Rate updated successfully.');
            }
        }
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionToggleLiftOn($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        $kesCost = (float) Yii::$app->config->get('lift_on_charges');
        $action = Yii::$app->request->post('action');

        if ($action === 'add') {
            $model->lift_charges += $kesCost; // ALWAYS ADD KES
            Yii::$app->session->setFlash('success', 'Lift On Charge Added.');
        } else {
            $model->lift_charges -= $kesCost;
            if ($model->lift_charges < 0) $model->lift_charges = 0;
            Yii::$app->session->setFlash('warning', 'Lift On Charge Removed.');
        }

        $model->save(false);
        $model->recalculateBalance();
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionToggleLiftOff($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        $kesCost = (float) Yii::$app->config->get('lift_off_charges');
        $action = Yii::$app->request->post('action');

        if ($action === 'add') {
            $model->lift_charges += $kesCost; // ALWAYS ADD KES
            Yii::$app->session->setFlash('success', 'Lift Off Charge Added.');
        } else {
            $model->lift_charges -= $kesCost;
            if ($model->lift_charges < 0) $model->lift_charges = 0;
            Yii::$app->session->setFlash('warning', 'Lift Off Charge Removed.');
        }

        $model->save(false);
        $model->recalculateBalance();
        return $this->redirect(['view', 'id' => $id]);
    }
    public function actionUpdateCreditDetails($id)
    {
        Yii::$app->user->can('dashboard-billing-update');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->updateAttributes([
                'atl_number' => $model->atl_number,
                'authorized_by' => $model->authorized_by
            ]);
            Yii::$app->session->setFlash('success', 'Credit Authorization details updated.');
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionGenerateInvoice($id)
    {
        $model = $this->findModel($id);
        $model->recalculateBalance();

        $header = $this->renderPartial('_invoice_header', ['model' => $model]);
        $footer = $this->renderPartial('_invoice_footer');
        $body   = $this->renderPartial('_invoice_body',   ['model' => $model]);

        $css = "
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .invoice-table th { background-color: #2c3e50; color: #ffffff; text-align: left; padding: 12px; font-size: 9pt; text-transform: uppercase; }
        .invoice-table td { padding: 10px; border-bottom: 1px solid #eee; font-size: 10pt; vertical-align: top; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .total-row { background-color: #f8f9fa; font-weight: bold; border-top: 2px solid #2c3e50 !important; }
        .section-title { border-bottom: 2px solid #eee; padding-bottom: 5px; margin-top: 25px; font-size: 12pt; color: #2c3e50; font-weight: bold; }
        .danger { color: #c0392b; }
        .success { color: #27ae60; }
        ";

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'destination' => Pdf::DEST_DOWNLOAD,
            'filename' => 'Invoice-' . $model->invoice_number . '.pdf',
            'content' => $body,
            'cssInline' => $css,
            'options' => [
                'title' => 'Invoice ' . $model->invoice_number,
                'tempDir' => Yii::getAlias('@runtime/mpdf'),
                'margin_top' => 55,
                'margin_bottom' => 30,
                'margin_header' => 15,
                'margin_footer' => 10,
            ],
            'methods' => [
                'SetHTMLHeader' => [$header],
                'SetHTMLFooter' => [$footer],
            ]
        ]);

        if (ob_get_length()) ob_end_clean();
        return $pdf->render();
    }

    public function actionCancelRequest($id)
    {
        $model = $this->findModel($id);

        $model->approval_status = 'NONE';
        $model->requester_note = null;
        $model->requested_by = null;
        $model->requested_at = null;

        if ($model->save(false)) {
            Yii::$app->session->setFlash('info', 'Credit request withdrawn.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to withdraw request.');
        }

        return $this->redirect(['view', 'id' => $model->bill_id]);
    }

    protected function findModel($bill_id)
    {
        if (($model = BillingRecords::findOne(['bill_id' => $bill_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
