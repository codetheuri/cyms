<?php

namespace dashboard\controllers;

use Yii;
use dashboard\models\BillingRecords;
use dashboard\models\BillingReversals;
use dashboard\models\BillingPayments;
use helpers\DashboardController;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class BillingReversalController extends DashboardController
{
    public $permissions = [
        'dashboard-billing-reversal-index' => 'View Reversals Log',
        'dashboard-billing-reversal-process' => 'Reverse Invoices',
    ];
   public function getViewPath()
   {
    return Yii::getAlias('@ui/views/cyms/billing'); 
   }
    public function actionIndex()
    {
        Yii::$app->user->can('dashboard-billing-delete'); // Or your specific reversal permission

        $dataProvider = new ActiveDataProvider([
            'query' => BillingReversals::find()->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        // Pointing to the view we created earlier
        return $this->render('reversals', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * PROCESS THE REVERSAL & INJECT NEGATIVE PAYMENT
     */
    public function actionProcess($id)
    {
        Yii::$app->user->can('dashboard-billing-delete'); 

        $model = BillingRecords::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested invoice does not exist.');
        }

        $reason = Yii::$app->request->post('reversal_reason');

        if (empty($reason)) {
            Yii::$app->session->setFlash('error', 'You must provide a reason for the audit trail.');
            return $this->redirect(['/dashboard/billing/view', 'id' => $id]);
        }

        // Use Database Transaction to ensure either EVERYTHING saves, or NOTHING saves
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // 1. Create the Audit Snapshot
            $reversal = new BillingReversals();
            $reversal->bill_id = $model->bill_id;
            $reversal->old_invoice_number = $model->invoice_number;
            $reversal->old_grand_total = $model->grand_total; // Stores KES master total
            $reversal->old_currency = $model->currency ?: 'KES';
            $reversal->reversal_reason = $reason;
            $reversal->reversed_by = Yii::$app->user->id;
            $reversal->snapshot_data = json_encode($model->attributes);
            
            if (!$reversal->save()) {
                throw new \Exception('Failed to save audit log.');
            }

            // 2. INJECT NEGATIVE PAYMENT TO REVERSE PREVIOUS CASH
            if ($model->total_paid > 0) {
                $negativePayment = new BillingPayments();
                $negativePayment->bill_id = $model->bill_id;
                $negativePayment->amount = -abs($model->total_paid); // Force it to be negative
                $negativePayment->transaction_date = date('Y-m-d');
                $negativePayment->method = 'REVERSAL'; // Custom method for your cashbook
                $negativePayment->reference = 'VOID: ' . $model->invoice_number;
                
                if (!$negativePayment->save(false)) {
                    throw new \Exception('Failed to process negative payment offset.');
                }
            }

            // 3. Void old invoice number & reset status
            $model->invoice_number = str_replace('INV-', 'REV-', $model->invoice_number) . '-' . rand(10,99);
            $model->status = 'UNPAID';
            $model->approval_status = 'NONE';
            $model->save(false);

            // 4. Recalculate Balance (This will sum the payments. Since we added a negative payment, total_paid becomes 0!)
            $model->recalculateBalance();

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Invoice reversed successfully! Previous payments have been offset, and the balance is reset.');

        } catch (\Exception $e) {
            $transaction->rollBack();
           //debug  error on debug index apge
           yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        // Send them back to the billing page so they can make corrections
        return $this->redirect(['/dashboard/billing/view', 'id' => $id]);
    }
}