<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model dashboard\models\BillingRecords */
/* @var $paymentModel dashboard\models\BillingPayments */

$this->title = 'Manage Invoice #' . $model->invoice_number;
$visit = $model->visit; // Shortcut to visit model
?>

<div class="block block-rounded content-card mb-3">
    <div class="block-content block-content-full d-flex justify-content-between align-items-center">
        
        <div>
            <div class="fs-sm text-muted text-uppercase fw-bold">Container Information</div>
            <div class="fs-3 fw-bold text-primary">
                <i class="fa fa-box me-2 text-muted"></i><?= $visit->container_number ?>
            </div>
            <div class="fs-sm text-muted">
                <i class="fa fa-clock me-1"></i> In: <strong><?= Yii::$app->formatter->asDate($visit->date_in) ?></strong>
                <span class="mx-2">|</span>
                <i class="fa fa-ticket-alt me-1"></i> Ticket: <strong><?= $visit->ticket_no_in ?></strong>
            </div>
        </div>

        <div class="text-end">
            <div class="fs-sm text-muted text-uppercase fw-bold">Bill To (Client)</div>
            <div class="fs-4 fw-bold">
                <?= $visit->containerOwner->owner_name ?? $visit->truck_owner_name_in ?>
            </div>
            <div class="fs-sm text-muted">
                <i class="fa fa-phone me-1"></i> <?= $visit->containerOwner->owner_contact ?? $visit->truck_owner_contact_in ?? 'No Contact' ?>
            </div>
        </div>

        <div class="text-end border-start ps-4 ms-4">
            <div class="fs-sm text-muted text-uppercase fw-bold">Invoice Status</div>
            <?php
            $badgeColor = match ($model->status) {
                'PAID' => 'success',
                'PARTIAL' => 'warning',
                'CREDIT' => 'info',
                default => 'danger'
            };
            ?>
            <span class="badge bg-<?= $badgeColor ?> fs-4"><?= $model->status ?></span>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-md-7">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default">
                <h3 class="block-title">Invoice Breakdown</h3>
                <div class="block-options">
                    <?php if ($model->status !== 'PAID' && $model->status !== 'CREDIT'): ?>
                        <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="modal" data-bs-target="#modal-discount">
                            <i class="fa fa-tag me-1"></i> Apply Discount
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="block-content">
                <table class="table table-bordered table-vcenter">
                    <thead class="bg-body-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-center" style="width: 100px;">Qty/Days</th>
                            <th class="text-end" style="width: 120px;">Rate</th>
                            <th class="text-end" style="width: 120px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="fw-bold">Storage Charges</div>
                                <div class="fs-xs text-muted">Daily Rate based on container size</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $model->storage_days ?></span>
                            </td>
                            <td class="text-end"><?= number_format($model->tariff_rate, 2) ?></td>
                            <td class="text-end fw-bold"><?= number_format($model->storage_total, 2) ?></td>
                        </tr>

                        <tr>
                            <td>
                                <div class="fw-bold">Repair Charges</div>
                                <div class="fs-xs text-muted">Derived from Survey Damages</div>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($model->repair_total, 2) ?></td>
                        </tr>

                        <tr>
                            <td>
                                <div class="fw-bold">Lift On/Off</div>
                                <div class="fs-xs text-muted">Handling fees</div>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-end">-</td>
                            <td class="text-end fw-bold"><?= number_format($model->lift_charges, 2) ?></td>
                        </tr>

                        <?php $subTotal = $model->storage_total + $model->repair_total + $model->lift_charges; ?>
                        <tr class="bg-body-light">
                            <td colspan="3" class="text-end fw-bold text-muted">Subtotal</td>
                            <td class="text-end fw-bold text-muted"><?= number_format($subTotal, 2) ?></td>
                        </tr>

                        <?php if ($model->discount_amount > 0): ?>
                            <tr class="text-danger bg-danger-light">
                                <td colspan="3" class="text-end fw-bold">
                                    <i class="fa fa-minus-circle me-1"></i> Less: Discount / Waiver
                                </td>
                                <td class="text-end fw-bold">-<?= number_format($model->discount_amount, 2) ?></td>
                            </tr>
                        <?php endif; ?>

                        <tr class="table-active" style="border-top: 2px solid #333;">
                            <td colspan="3" class="text-end fs-5 fw-bold text-uppercase">Grand Total</td>
                            <td class="text-end fs-4 fw-bold text-primary"><?= number_format($model->grand_total, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <h5 class="mt-4 mb-2 border-bottom pb-2">Payment History</h5>
                <table class="table table-sm table-hover table-striped mb-0">
                    <thead>
                        <tr class="fs-sm text-muted">
                            <th>Date</th>
                            <th>Ref</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($model->payments as $payment): ?>
                            <tr>
                                <td class="fs-sm"><?= Yii::$app->formatter->asDate($payment->transaction_date) ?></td>
                                <td class="fs-sm">
                                    <span class="fw-bold"><?= $payment->method ?></span>
                                    <span class="text-muted ms-1"><?= $payment->reference ?></span>
                                </td>
                                <td class="text-end text-success fw-bold"><?= number_format($payment->amount, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($model->payments)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3 fs-sm">No payments recorded yet.</td>
                            </tr>
                        <?php endif; ?>

                        <tr class="table-dark">
                            <td colspan="2" class="text-end fw-bold text-white">Balance Due:</td>
                            <td class="text-end fw-bold text-white"><?= number_format($model->balance, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">

        <?php if ($model->balance > 0.01 && $model->status !== 'PAID' ): ?>
            <div class="block block-rounded content-card border-start border-5 border-success mb-3">
                <div class="block-header bg-body-light">
                    <h3 class="block-title text-success"><i class="fa fa-money-bill-wave me-2"></i> Record Payment</h3>
                </div>
                <div class="block-content block-content-full">
                    <?php $form = ActiveForm::begin(['action' => ['payment', 'id' => $model->bill_id]]); ?>

                    <div class="mb-3">
                        <label class="form-label">Amount to Pay (Max: <?= number_format($model->balance) ?>)</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold">KES</span>
                            <?= $form->field($paymentModel, 'amount', ['options' => ['tag' => false]])->textInput([
                                'type' => 'number', 
                                'step' => '0.01', 
                                'max' => $model->balance, 
                                'value' => $model->balance, 
                                'class' => 'form-control form-control-lg'
                            ])->label(false) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <?= $form->field($paymentModel, 'transaction_date')->input('date') ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($paymentModel, 'method')->dropDownList(['CASH' => 'Cash', 'MPESA' => 'M-Pesa', 'BANK' => 'Bank', 'CHEQUE' => 'Cheque']) ?>
                        </div>
                    </div>

                    <?= $form->field($paymentModel, 'reference')->textInput(['placeholder' => 'Ref / Cheque No']) ?>

                    <button type="submit" class="btn btn-success w-100 mt-3 btn-lg">
                        <i class="fa fa-check me-1"></i> Confirm Payment
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($model->status === 'PAID'): ?>
            <div class="alert alert-success d-flex align-items-center justify-content-center p-4 mb-3">
                <div class="text-center">
                    <i class="fa fa-check-circle fa-3x mb-2"></i>
                    <h4 class="mb-0">Fully Paid</h4>
                    <p class="mb-0 mt-2 fs-sm">Container is cleared for release.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($model->status !== 'PAID' && $model->status !== 'CREDIT'): ?>
            <div class="block block-rounded content-card border-start border-5 border-warning mb-3">
                <div class="block-header bg-warning-light">
                    <h3 class="block-title text-warning-dark"><i class="fa fa-file-signature me-2"></i> Authorize Credit Exit</h3>
                </div>
                <div class="block-content block-content-full">
                    <p class="fs-sm text-muted mb-3">
                        Allow container to leave without payment? Requires supervisor name and signed agreement.
                    </p>
                    <?php $form = ActiveForm::begin([
                        'action' => ['authorize-credit', 'id' => $model->bill_id],
                        'options' => ['enctype' => 'multipart/form-data']
                    ]); ?>
                    
                    <div class="mb-3">
                        <?= $form->field($model, 'authorized_by')->textInput(['placeholder' => 'Supervisor Name']) ?>
                    </div>
                    <div class="mb-3">
                        <?= $form->field($model, 'agreement_file')->fileInput(['required' => true])->label('Upload Agreement (PDF/Img)') ?>
                    </div>
                    
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fa fa-check me-1"></i> Authorize & Release
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php elseif ($model->status === 'CREDIT'): ?>
            <div class="alert alert-info mb-3">
                <h4 class="alert-heading fs-5"><i class="fa fa-info-circle me-1"></i> Credit Active</h4>
                <p class="fs-sm mb-2">
                    Authorized By: <strong><?= Html::encode($model->authorized_by) ?></strong>
                </p>
                <?php if ($model->credit_agreement_path): ?>
                    <a href="<?= Yii::getAlias('@web') . '/' . $model->credit_agreement_path ?>" target="_blank" class="btn btn-sm btn-alt-info">
                        <i class="fa fa-file-download me-1"></i> View Agreement
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="d-grid">
             <?= Html::a('<i class="fa fa-truck-moving me-1"></i> Go to Gate OUT', ['/dashboard/visit/out-index'], ['class' => 'btn btn-alt-secondary btn-lg']) ?>
        </div>

    </div>
</div>

<div class="modal fade" id="modal-discount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['action' => ['update-discount', 'id' => $model->bill_id]]); ?>
            <div class="block block-rounded shadow-none mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Apply Discount</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="block-content fs-sm">
                    <div class="mb-4">
                        <label class="form-label">Discount Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">KES</span>
                            <?= $form->field($model, 'discount_amount', ['options' => ['tag' => false]])->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control form-control-lg'])->label(false) ?>
                        </div>
                        <div class="form-text text-muted">Max allowed: <?= number_format($model->storage_total + $model->repair_total + $model->lift_charges, 2) ?></div>
                    </div>
                </div>
                <div class="block-content block-content-full block-content-sm text-end border-top">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Discount</button>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>