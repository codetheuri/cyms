<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Manage Invoice #' . $model->bill_id;
?>

<div class="row">
    <div class="col-md-8">
        <div class="block block-rounded content-card">
            <div class="block-header block-header-default">
                <h3 class="block-title">Invoice Details: <span class="text-primary"><?= $model->visit->container_number ?></span></h3>
                <h3 class="block-title fs-sm">Client: <span class="text-muted"><?= $model->visit->truck_owner_name_in ?></span></h3>
                <div class="block-options">
                    <?php 
                        $badgeColor = match($model->status) {
                            'PAID' => 'success',
                            'PARTIAL' => 'warning',
                            'CREDIT' => 'info',
                            default => 'danger'
                        };
                    ?>
                    <span class="badge bg-<?= $badgeColor ?> fs-sm"><?= $model->status ?></span>
                </div>
            </div>
            <div class="block-content">
                <table class="table table-bordered table-striped">
                    <thead class="bg-body-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-center">Qty/Days</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Storage Charges</td>
                            <td class="text-center"><?= $model->storage_days ?></td>
                            <td class="text-end"><?= number_format($model->tariff_rate, 2) ?></td>
                            <td class="text-end fw-bold"><?= number_format($model->storage_total, 2) ?></td>
                        </tr>
                        <tr>
                            <td>Repair Charges (Survey)</td>
                            <td class="text-center">1</td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($model->repair_total, 2) ?></td>
                        </tr>
                        <tr>
                            <td>Lift On/Off Charges</td>
                            <td class="text-center">1</td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($model->lift_charges, 2) ?></td>
                        </tr>
                        <tr class="table-active fs-5">
                            <td colspan="3" class="text-end fw-bold">Grand Total</td>
                            <td class="text-end fw-bold text-primary"><?= number_format($model->grand_total, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <h5 class="mt-4 mb-3 border-bottom pb-2">Payment History</h5>
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($model->payments as $payment): ?>
                        <tr>
                            <td><?= Yii::$app->formatter->asDate($payment->transaction_date) ?></td>
                            <td><?= $payment->method ?></td>
                            <td><?= $payment->reference ?></td>
                            <td class="text-end text-success"><?= number_format($payment->amount, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($model->payments)): ?>
                            <tr><td colspan="4" class="text-center text-muted">No payments recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold border-top">
                            <td colspan="3" class="text-end">Total Paid:</td>
                            <td class="text-end text-success"><?= number_format($model->total_paid, 2) ?></td>
                        </tr>
                        <tr class="fw-bold fs-4 text-danger">
                            <td colspan="3" class="text-end">Balance Due:</td>
                            <td class="text-end"><?= number_format($model->balance, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        
        <?php if ($model->balance > 0.01 || $model->status === 'UNPAID' || $model->status === 'PARTIAL'): ?>
        <div class="block block-rounded content-card bg-body-light border-start border-5 border-success mb-4">
            <div class="block-header">
                <h3 class="block-title">Add Payment</h3>
            </div>
            <div class="block-content block-content-full">
                <?php $form = ActiveForm::begin(['action' => ['payment', 'id' => $model->bill_id]]); ?>
                
                <?= $form->field($paymentModel, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'value' => $model->balance])->label('Amount to Pay') ?>
                
                <?= $form->field($paymentModel, 'transaction_date')->input('date') ?>
                
                <?= $form->field($paymentModel, 'method')->dropDownList([
                    'CASH' => 'Cash',
                    'MPESA' => 'M-Pesa',
                    'BANK' => 'Bank Transfer',
                    'CHEQUE' => 'Cheque',
                ]) ?>
                
                <?= $form->field($paymentModel, 'reference')->textInput(['placeholder' => 'e.g. M-Pesa Code']) ?>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa fa-check me-1"></i> Record Payment
                    </button>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($model->status !== 'PAID' && $model->status !== 'CREDIT'): ?>
        <div class="block block-rounded content-card border-start border-5 border-warning mb-4">
            <div class="block-header">
                <h3 class="block-title text-warning">Authorize Credit Exit</h3>
            </div>
            <div class="block-content block-content-full">
                <p class="fs-sm text-muted mb-3">
                    Allow container to leave without full payment. Requires Supervisor approval and signed agreement.
                </p>

                <?php $form = ActiveForm::begin([
                    'action' => ['authorize-credit', 'id' => $model->bill_id], 
                    'options' => ['enctype' => 'multipart/form-data']
                ]); ?>
                
                <?= $form->field($model, 'authorized_by')->textInput(['placeholder' => 'Supervisor Name']) ?>
                
                <?= $form->field($model, 'agreement_file')->fileInput(['required' => true])->label('Signed Agreement') ?>

                <div class="mt-3">
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fa fa-file-signature me-1"></i> Authorize Exit
                    </button>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <?php elseif ($model->status === 'CREDIT'): ?>
            <div class="alert alert-info mb-4">
                <strong><i class="fa fa-check-circle"></i> Credit Authorized</strong><br>
                By: <?= Html::encode($model->authorized_by) ?><br>
                <?php if ($model->credit_agreement_path): ?>
                    <a href="<?= Yii::getAlias('@web').'/'.$model->credit_agreement_path ?>" target="_blank" class="alert-link">View Agreement</a>
                <?php endif; ?>
            </div>
        <?php elseif ($model->status === 'PAID'): ?>
            <div class="alert alert-success d-flex align-items-center justify-content-center p-4 mb-4">
                <div class="text-center">
                    <i class="fa fa-check-circle fa-3x mb-2"></i>
                    <h4 class="mb-0">Fully Paid</h4>
                    <p class="mb-0 mt-2">Container is ready for release.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-3 d-grid gap-2">
            <?= Html::a('Back to Gate OUT', ['/dashboard/visit/out-index'], ['class' => 'btn btn-alt-secondary']) ?>
        </div>
    </div>
</div>