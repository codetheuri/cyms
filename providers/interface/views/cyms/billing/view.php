<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model dashboard\models\BillingRecords */
/* @var $paymentModel dashboard\models\BillingPayments */

$this->title = 'Invoice #' . $model->invoice_number;
$visit = $model->visit;

// --- 1. SETTINGS & LOGIC FOR LIFT SPLIT ---
$settingLiftOn  = (float) Yii::$app->config->get('lift_on_charges');
$settingLiftOff = (float) Yii::$app->config->get('lift_off_charges');
$currentLift    = (float) ($model->lift_charges ?? 0);

// Lift Logic
$hasLiftOff = ($currentLift >= $settingLiftOff);
$hasLiftOn = ($currentLift >= ($settingLiftOff + $settingLiftOn)) ||
    (abs($currentLift - $settingLiftOn) < 0.01 && !$hasLiftOff);

// --- 2. CALCULATE TOTALS ---
$storage  = (float)$model->storage_total;
$repair   = (float)$model->repair_total;
$subTotal = $storage + $repair + $currentLift;

$discount = (float)$model->discount_amount;
$grandTotal = $subTotal - $discount;

// Status Badge Logic
$badgeColor = match ($model->status) {
    'PAID' => 'success',
    'PARTIAL' => 'warning',
    'CREDIT' => 'info',
    default => 'danger'
};

// Date/Time Helper
$dateIn = Yii::$app->formatter->asDate($visit->date_in);
$timeIn = $visit->time_in ? date('H:i', strtotime($visit->time_in)) . ' hrs' : '';

// 3. CAPTURE RETURN PARAMETER
$returnClientId = Yii::$app->request->get('return_client');
?>

<div class="bg-body-light border-bottom mb-4">
    <div class="content py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-grow-1 fs-3 fw-bold my-2 my-sm-3">
                <i class="fa fa-file-invoice me-2 text-muted"></i> Invoice <span class="text-muted fw-light">#<?= Html::encode($model->invoice_number) ?></span>
            </h1>
            <nav class="flex-shrink-0 my-2 my-sm-0 ms-sm-3">
                <?php if ($returnClientId): ?>
                    <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back to Client', 
                        ['/dashboard/container-owner/view', 'id' => $returnClientId, '#' => 'tab-unpaid'], 
                        ['class' => 'btn btn-alt-secondary px-4 fw-bold shadow-sm']
                    ) ?>
                <?php else: ?>
                    <?= Html::a('<i class="fa fa-arrow-left me-1"></i> Back to List', 
                        ['index'], 
                        ['class' => 'btn btn-alt-secondary px-4 fw-bold shadow-sm']
                    ) ?>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="block block-rounded h-100 mb-0 shadow-sm border-start border-4 border-primary">
            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-sm fw-semibold text-uppercase text-muted">Container</div>
                    <div class="fs-2 fw-bold text-dark"><?= $visit->container_number ?></div>
                    <div class="fs-sm text-muted">
                        <span class="badge bg-secondary"><?= $visit->containerType->iso_code ?? 'Type N/A' ?></span>
                        <span class="ms-1"><i class="fa fa-ticket-alt me-1"></i> <?= $visit->ticket_no_in ?></span>
                    </div>
                </div>
                <div class="ms-3 item item-circle bg-primary-light text-primary">
                    <i class="fa fa-box fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-4">
        <div class="block block-rounded h-100 mb-0 shadow-sm border-start border-4 border-warning">
            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-sm fw-semibold text-uppercase text-muted">Billable Days</div>
                    <div class="fs-2 fw-bold text-dark"><?= $model->storage_days ?> <small class="fs-lg text-muted fw-normal">Days</small></div>
                    <div class="fs-sm text-muted">
                        <i class="fa fa-calendar-alt me-1"></i> In: <?= $dateIn ?>
                    </div>
                </div>
                <div class="ms-3 item item-circle bg-warning-light text-warning">
                    <i class="fa fa-clock fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-xl-4">
        <div class="block block-rounded h-100 mb-0 shadow-sm border-start border-4 border-<?= $badgeColor ?>">
            <div class="block-content block-content-full d-flex align-items-center justify-content-between">
                <div>
                    <div class="fs-sm fw-semibold text-uppercase text-muted">Invoice Status</div>
                    <span class="badge bg-<?= $badgeColor ?> fs-5 px-3 py-2 rounded-pill mt-1">
                        <?= $model->status ?>
                    </span>
                     <div class="fs-sm text-muted mt-1 text-truncate" style="max-width: 200px;">
                        <i class="fa fa-user me-1"></i> <?= $visit->containerOwner->owner_name ?? $visit->truck_owner_name_in ?>
                    </div>
                </div>
                <div class="ms-3 item item-circle bg-<?= $badgeColor ?>-light text-<?= $badgeColor ?>">
                    <i class="fa fa-file-invoice-dollar fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="block block-rounded h-100 shadow-sm">
            <div class="block-header block-header-default">
                <h3 class="block-title fw-bold">Charges Breakdown</h3>
                <div class="block-options">
                     <?= Html::a('<i class="fa fa-print"></i>',
                        ['/dashboard/billing/generate-invoice', 'id' => $model->bill_id],
                        ['class' => 'btn btn-sm btn-alt-secondary', 'title'=>'Print Invoice']
                    ) ?>
                </div>
            </div>
            
            <div class="block-content p-0">
                <table class="table table-vcenter table-borderless mb-0">
                    <thead class="bg-body-light border-bottom">
                        <tr class="text-uppercase fs-xs text-muted">
                            <th class="ps-4 py-3">Description</th>
                            <th class="text-center py-3">Qty</th>
                            <th class="text-end py-3">Rate</th>
                            <th class="text-end pe-4 py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-bottom border-light">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">Storage Charges</div>
                                <div class="fs-xs text-muted">Daily Storage Fee</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-body text-dark border"><?= $model->storage_days ?></span>
                            </td>
                            <td class="text-end text-muted">
                                <?= number_format($model->tariff_rate, 2) ?>
                                <?php if ($model->status !== 'PAID'): ?>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modal-rate" class="fs-xs text-primary ms-1" title="Edit Rate"><i class="fa fa-pen"></i></a>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold pe-4 font-monospace"><?= number_format($storage, 2) ?></td>
                        </tr>

                        <tr class="border-bottom border-light">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">Lift Off (Entry)</div>
                                <div class="fs-xs text-muted">Truck Offloading</div>
                            </td>
                            <td class="text-center">
                                <?php if ($model->status !== 'PAID'): ?>
                                    <?php if ($hasLiftOff): ?>
                                        <?= Html::a('<i class="fa fa-minus"></i>', 
                                            ['toggle-lift-off', 'id' => $model->bill_id], 
                                            [
                                                'class' => 'btn btn-sm btn-alt-danger rounded-circle px-2', 
                                                'title' => 'Remove Charge', 
                                                'data-method'=>'post',
                                                'data-params' => ['action' => 'remove']
                                            ]
                                        ) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fa fa-plus"></i>', 
                                            ['toggle-lift-off', 'id' => $model->bill_id], 
                                            [
                                                'class' => 'btn btn-sm btn-alt-success rounded-circle px-2', 
                                                'title' => 'Add Charge', 
                                                'data-method'=>'post',
                                                'data-params' => ['action' => 'add']
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fa <?= $hasLiftOff ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-muted"><?= number_format($settingLiftOff, 2) ?></td>
                            <td class="text-end fw-bold pe-4 font-monospace"><?= ($hasLiftOff) ? number_format($settingLiftOff, 2) : '0.00' ?></td>
                        </tr>

                        <tr class="border-bottom border-light">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark">Lift On (Exit)</div>
                                <div class="fs-xs text-muted">Truck Loading</div>
                            </td>
                            <td class="text-center">
                                <?php if ($model->status !== 'PAID'): ?>
                                    <?php if ($hasLiftOn): ?>
                                        <?= Html::a('<i class="fa fa-minus"></i>', 
                                            ['toggle-lift-on', 'id' => $model->bill_id], 
                                            [
                                                'class' => 'btn btn-sm btn-alt-danger rounded-circle px-2', 
                                                'title' => 'Remove Charge', 
                                                'data-method'=>'post',
                                                'data-params' => ['action' => 'remove']
                                            ]
                                        ) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fa fa-plus"></i>', 
                                            ['toggle-lift-on', 'id' => $model->bill_id], 
                                            [
                                                'class' => 'btn btn-sm btn-alt-success rounded-circle px-2', 
                                                'title' => 'Add Charge', 
                                                'data-method'=>'post',
                                                'data-params' => ['action' => 'add']
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fa <?= $hasLiftOn ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-muted"><?= number_format($settingLiftOn, 2) ?></td>
                            <td class="text-end fw-bold pe-4 font-monospace"><?= ($hasLiftOn) ? number_format($settingLiftOn, 2) : '0.00' ?></td>
                        </tr>

                        <?php if ($repair > 0): ?>
                            <tr class="border-bottom border-light">
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark">Repair Charges</div>
                                    <div class="fs-xs text-muted">Damage Survey</div>
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end fw-bold pe-4 font-monospace"><?= number_format($repair, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    
                    <tfoot class="bg-body-light">
                        <tr>
                            <td colspan="3" class="text-end text-muted fw-bold pt-3 pb-1">Subtotal</td>
                            <td class="text-end fw-bold pt-3 pb-1 pe-4 font-monospace"><?= number_format($subTotal, 2) ?></td>
                        </tr>
                        
                        <tr>
                            <td colspan="3" class="text-end pb-3">
                                <?php if ($discount > 0): ?>
                                    <span class="text-danger fw-bold fs-sm"><i class="fa fa-minus-circle me-1"></i> Discount</span>
                                    <?php if ($model->status !== 'PAID'): ?>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-discount" class="fs-xs ms-1 text-muted"><i class="fa fa-pen"></i></a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($model->status !== 'PAID'): ?>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-discount" class="fs-xs fw-bold text-primary">
                                            Add Discount
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-danger pb-3 pe-4 font-monospace">
                                <?= $discount > 0 ? '-' . number_format($discount, 2) : '' ?>
                            </td>
                        </tr>
                        
                        <tr class="bg-primary-dark text-white">
                            <td colspan="3" class="text-end fs-5 fw-bold text-uppercase py-3">Grand Total</td>
                            <td class="text-end fs-4 fw-bold py-3 pe-4 font-monospace"><?= number_format($grandTotal, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="bg-white p-4">
                    <h6 class="text-uppercase text-muted fs-xs fw-bold border-bottom pb-2 mb-3">
                        <i class="fa fa-history me-1"></i> Payment History
                    </h6>
                    <table class="table table-striped table-sm mb-0 fs-sm">
                        <tbody>
                            <?php if (empty($model->payments)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted fst-italic py-2">No payments received yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($model->payments as $payment): ?>
                                    <tr>
                                        <td><i class="fa fa-calendar me-2 text-muted"></i><?= Yii::$app->formatter->asDate($payment->transaction_date) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-gray-light text-dark border"><?= $payment->method ?></span>
                                            <span class="text-muted ms-1 fs-xs"><?= $payment->reference ?></span>
                                        </td>
                                        <td class="text-end fw-bold text-success font-monospace"><?= number_format($payment->amount, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr class="bg-body-light">
                                <td colspan="2" class="text-end fw-bold">Balance Remaining:</td>
                                <td class="text-end fw-bold text-danger font-monospace"><?= number_format($model->balance, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">

        <?php if ($model->balance > 0.01 && $model->status !== 'PAID'): ?>
            <div class="block block-rounded shadow-sm border-top border-5 border-success mb-3">
                <div class="block-header bg-body-light">
                    <h3 class="block-title fw-bold text-success"><i class="fa fa-cash-register me-2"></i> Receive Payment</h3>
                </div>
                <div class="block-content block-content-full">
                    <?php $form = ActiveForm::begin(['action' => ['payment', 'id' => $model->bill_id, 'return_client' => $returnClientId]]); ?>

                    <div class="mb-3">
                        <label class="form-label text-muted fs-sm text-uppercase">Amount to Pay</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white fw-bold border-success">KES</span>
                            <?= $form->field($paymentModel, 'amount', ['options' => ['tag' => false]])->textInput([
                                'type' => 'number', 'step' => '0.01', 'max' => $model->balance, 'value' => $model->balance,
                                'class' => 'form-control fw-bold border-success'
                            ])->label(false) ?>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <?= $form->field($paymentModel, 'transaction_date')->input('date', ['class' => 'form-control form-control-alt']) ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($paymentModel, 'method')->dropDownList(
                                ['CASH' => 'Cash', 'MPESA' => 'M-Pesa', 'BANK' => 'Bank', 'CHEQUE' => 'Cheque'],
                                ['class' => 'form-select form-select-alt']
                            ) ?>
                        </div>
                    </div>

                    <?= $form->field($paymentModel, 'reference')->textInput(['placeholder' => 'Reference / Cheque No.', 'class' => 'form-control form-control-alt']) ?>

                    <button type="submit" class="btn btn-success w-100 mt-3 py-2 fw-bold shadow-sm">
                        <i class="fa fa-check me-1"></i> Confirm Payment
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($model->status === 'PAID'): ?>
            <div class="block block-rounded bg-success-light mb-3 border-start border-5 border-success">
                <div class="block-content block-content-full d-flex align-items-center justify-content-between p-4">
                    <div>
                        <h4 class="fw-bold text-success mb-1">Bill Fully Paid</h4>
                        <p class="text-success-dark mb-0 fs-sm">Container is cleared for exit.</p>
                    </div>
                    <div class="item item-circle bg-success text-white">
                        <i class="fa fa-check fa-2x"></i>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($model->status !== 'PAID' && $model->status !== 'CREDIT'): ?>
            <div class="block block-rounded border border-warning mb-3">
                <div class="block-content block-content-full bg-warning-light">
                    
                    <?php if (($model->approval_status ?? 'NONE') === 'REJECTED'): ?>
                        <div class="alert alert-danger d-flex align-items-center mb-3">
                            <i class="fa fa-times-circle fa-2x me-3"></i>
                            <div>
                                <div class="fw-bold">Request Rejected</div>
                                <div class="fs-sm"><?= Html::encode($model->rejection_reason) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($model->approval_status === 'PENDING'): ?>
                         <div class="text-center py-3">
                            <div class="spinner-border text-warning mb-2" role="status"></div>
                            <h5 class="fw-bold text-dark mb-1">Waiting Approval</h5>
                            <p class="text-muted fs-sm mb-3">Request sent to Supervisor.</p>
                            <?= Html::a('<i class="fa fa-times me-1"></i> Withdraw Request', ['cancel-request', 'id' => $model->bill_id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data-method' => 'post',
                                'data-confirm' => 'Are you sure you want to withdraw this request?'
                            ]) ?>
                        </div>

                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 fw-bold text-warning-dark"><i class="fa fa-hand-holding-usd me-1"></i> Request Credit</h5>
                        </div>
                        <p class="fs-sm text-muted mb-2">Can't pay now? Request a credit release.</p>
                        <?php $form = ActiveForm::begin(['action' => ['request-credit', 'id' => $model->bill_id]]); ?>
                            <div class="mb-3">
                                <?= $form->field($model, 'requester_note')->textarea([
                                    'rows' => 2,
                                    'placeholder' => 'Reason (Optional)...',
                                    'class' => 'form-control border-warning'
                                ])->label(false) ?>
                            </div>
                            <button type="submit" class="btn btn-warning w-100 fw-bold text-dark">
                                <i class="fa fa-paper-plane me-1"></i> Send Request
                            </button>
                        <?php ActiveForm::end(); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($model->status === 'CREDIT'): ?>
            <div class="block block-rounded bg-info-light mb-3 border-start border-5 border-info">
                <div class="block-content block-content-full">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h5 class="text-info-dark fw-bold mb-0"><i class="fa fa-info-circle me-1"></i> Credit Authorized</h5>
                        <i class="fa fa-stamp fa-2x text-info opacity-25"></i>
                    </div>
                    <div class="row fs-sm text-muted">
                        <div class="col-6">Approved By:<br><strong class="text-dark"><?= Html::encode($model->authorized_by) ?></strong></div>
                        <div class="col-6">ATL No:<br><strong class="text-dark"><?= Html::encode($model->atl_number) ?></strong></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-2">
            <?php if ($model->balance <= 0.01 || $model->status === 'CREDIT'): ?>
                <div class="col-12">
                    <?= Html::a('<i class="fa fa-truck-moving me-2"></i> Proceed to Gate OUT',
                        ['/dashboard/visit/gate-out', 'id' => $visit->visit_id], 
                        ['class' => 'btn btn-success w-100 py-3 shadow fw-bold pulse-success']
                    ) ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<div class="modal fade" id="modal-discount" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['action' => ['update-discount', 'id' => $model->bill_id]]); ?>
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Apply Discount</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 fs-sm">Maximum discount allowed: <strong><?= number_format($subTotal, 2) ?></strong></div>
                <?= $form->field($model, 'discount_amount')->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control form-control-lg'])->label('Amount to Waive (KES)') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-bold">Apply Waiver</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-rate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['action' => ['update-rate', 'id' => $model->bill_id]]); ?>
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Adjust Daily Rate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 fs-sm mb-3"><i class="fa fa-exclamation-triangle me-1"></i> This will recalculate the entire storage bill for <?= $model->storage_days ?> days.</div>
                <?= $form->field($model, 'tariff_rate')->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control form-control-lg'])->label('Daily Rate (KES)') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning fw-bold text-dark">Update Rate</button>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<style>
    /* Simple Pulse Animation for Gate Out Button */
    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
        100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
    .pulse-success {
        animation: pulse-green 2s infinite;
    }
    .font-monospace { font-family: monospace; letter-spacing: -0.5px; }
</style>