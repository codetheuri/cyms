<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model dashboard\models\BillingRecords */
/* @var $paymentModel dashboard\models\BillingPayments */

$this->title = 'Invoice #' . $model->invoice_number;
$visit = $model->visit;
$isSwapping = ($visit->shipping_line_id == 9);

// --- 1. CURRENCY LOGIC ---
$isUsd  = ($model->currency === 'USD');
$curr   = $model->currency ?: 'KES';
$sym    = $isUsd ? '$' : 'KES ';
$flag   = $isUsd ? '🇺🇸' : '🇰🇪';
$exRate = $model->exchange_rate > 0 ? $model->exchange_rate : 1.00;

// --- 2. DISPLAY LOGIC (Converting from KES DB) ---
$kesLiftOn  = (float) Yii::$app->config->get('lift_on_charges');
$kesLiftOff = (float) Yii::$app->config->get('lift_off_charges');

$settingLiftOn  = $isUsd ? round($kesLiftOn / $exRate, 2) : $kesLiftOn;
$settingLiftOff = $isUsd ? round($kesLiftOff / $exRate, 2) : $kesLiftOff;

$currentLiftKes = (float) ($model->lift_charges ?? 0);
$displayLift = $isUsd ? ($currentLiftKes / $exRate) : $currentLiftKes;

$hasLiftOff = ($currentLiftKes > 0.01);
$hasLiftOn = ($currentLiftKes > ($kesLiftOff + 0.1));

$displayRate = $isUsd ? ($model->tariff_rate / $exRate) : $model->tariff_rate;
$storage     = $isUsd ? ($model->storage_total / $exRate) : $model->storage_total;
$repair      = $isUsd ? ($model->repair_total / $exRate) : $model->repair_total; 

$subTotal    = $storage + $repair + $displayLift;

$discount    = $isUsd ? ($model->discount_amount / $exRate) : $model->discount_amount;
$grandTotal  = $isUsd ? $model->foreign_grand_total : $model->grand_total;
$balance     = $isUsd ? $model->foreign_balance : $model->balance;

$badgeColor = match ($model->status) {
    'PAID' => 'success',
    'PARTIAL' => 'warning',
    'CREDIT' => 'info',
    default => 'danger'
};

$dateIn = Yii::$app->formatter->asDate($visit->date_in);
$timeIn = $visit->time_in ? date('H:i', strtotime($visit->time_in)) . ' hrs' : '';
$returnClientId = Yii::$app->request->get('return_client');

// --- CONTAINER REMARKS / FLAGS LOGIC ---
$containerFlagComment = $visit->comments_in ?? null; 
?>

<div class="bg-body-light border-bottom mb-4">
    <div class="content py-3">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center">
            <h1 class="flex-grow-1 fs-3 fw-bold my-2 my-sm-3">
                <i class="fa fa-file-invoice me-2 text-muted"></i> Invoice <span class="text-muted fw-light">#<?= Html::encode($model->invoice_number) ?></span>
                <span class="badge bg-primary-light text-primary fs-sm ms-2 border border-primary">
                    <span class="me-1 fs-6"><?= $flag ?></span> <?= $curr ?> BILLING
                </span>
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

<?php if (!empty($containerFlagComment)): ?>
<div class="alert alert-warning d-flex align-items-center justify-content-between mb-4 shadow-sm border-start border-warning border-4" role="alert">
    <div class="d-flex align-items-center">
        <div class="item item-circle bg-warning text-white me-3 fs-3">
            <i class="fa fa-exclamation-triangle"></i>
        </div>
        <div>
            <h4 class="alert-heading fs-6 fw-bold mb-1 text-warning-dark text-uppercase">Container Flag / Comment</h4>
            <p class="mb-0 fw-medium text-dark"><?= Html::encode($containerFlagComment) ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

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
                <h3 class="block-title fw-bold">Charges Breakdown <span class="badge bg-body-dark text-dark ms-2"><?= $curr ?></span></h3>
                <div class="block-options">
                    <?php if (in_array($model->status, ['PAID', 'CREDIT']) && $visit->status === 'GATE_OUT'): ?>
                        <button type="button" class="btn btn-sm btn-alt-danger me-2" data-bs-toggle="modal" data-bs-target="#modal-reverse">
                            <i class="fa fa-undo"></i> Reverse & Rebill
                        </button>
                    <?php endif; ?>
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
                            <th class="text-end py-3">Rate (<?= $curr ?>)</th>
                            <th class="text-end pe-4 py-3">Total (<?= $curr ?>)</th>
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
                                <?php if ($isSwapping): ?>
                                    <span class="badge bg-info-light text-info fw-bold"><i class="fa fa-lock me-1"></i> FIXED SWAP RATE</span>
                                <?php else: ?>
                                    <?= $sym . number_format($displayRate, 2) ?>
                                    <?php if ($model->status !== 'PAID'): ?>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-rate" class="fs-xs text-primary ms-1" title="Edit Rate"><i class="fa fa-pen"></i></a>
                                    <?php endif; ?>
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
                                        <?= Html::a('<i class="fa fa-minus"></i>', ['toggle-lift-off', 'id' => $model->bill_id], ['class' => 'btn btn-sm btn-alt-danger rounded-circle px-2', 'title' => 'Remove Charge', 'data-method'=>'post', 'data-params' => ['action' => 'remove']]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fa fa-plus"></i>', ['toggle-lift-off', 'id' => $model->bill_id], ['class' => 'btn btn-sm btn-alt-success rounded-circle px-2', 'title' => 'Add Charge', 'data-method'=>'post', 'data-params' => ['action' => 'add']]) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fa <?= $hasLiftOff ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-muted"><?= $sym . number_format($settingLiftOff, 2) ?></td>
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
                                        <?= Html::a('<i class="fa fa-minus"></i>', ['toggle-lift-on', 'id' => $model->bill_id], ['class' => 'btn btn-sm btn-alt-danger rounded-circle px-2', 'title' => 'Remove Charge', 'data-method'=>'post', 'data-params' => ['action' => 'remove']]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fa fa-plus"></i>', ['toggle-lift-on', 'id' => $model->bill_id], ['class' => 'btn btn-sm btn-alt-success rounded-circle px-2', 'title' => 'Add Charge', 'data-method'=>'post', 'data-params' => ['action' => 'add']]) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fa <?= $hasLiftOn ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-muted"><?= $sym . number_format($settingLiftOn, 2) ?></td>
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
                            <td colspan="3" class="text-end fs-5 fw-bold text-uppercase py-3">Grand Total (<?= $curr ?>)</td>
                            <td class="text-end fs-4 fw-bold py-3 pe-4 font-monospace"><?= $sym . number_format($grandTotal, 2) ?></td>
                        </tr>

                        <?php if ($isUsd): ?>
                        <tr>
                            <td colspan="4" class="text-end pb-3 pt-2 pe-4 bg-white border-top">
                                <div class="fs-sm text-muted fst-italic">
                                    Exchange Rate Applied: 1 USD = <?= number_format($exRate, 2) ?> KES<br>
                                    <strong class="text-dark">Total in Local Currency: KES <?= number_format($model->grand_total, 2) ?></strong>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
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
                                            <?php if ($payment->method === 'REVERSAL'): ?>
                                                <span class="badge bg-danger-light text-danger border-danger">REVERSAL</span>
                                            <?php else: ?>
                                                <span class="badge bg-gray-light text-dark border"><?= Html::encode($payment->method) ?></span>
                                            <?php endif; ?>
                                            <span class="text-muted ms-1 fs-xs"><?= Html::encode($payment->reference) ?></span>
                                        </td>
                                        <td class="text-end fw-bold <?= $payment->amount < 0 ? 'text-danger' : 'text-success' ?> font-monospace">
                                            <?php 
                                            // Show payment in the billing currency
                                            $paidAmt = $isUsd ? ($payment->amount / $exRate) : $payment->amount;
                                            echo $sym . number_format($paidAmt, 2);
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr class="bg-body-light">
                                <td colspan="2" class="text-end fw-bold">Balance Remaining:</td>
                                <td class="text-end fw-bold text-danger font-monospace fs-5"><?= $sym . number_format($balance, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">

        <?php if ($balance > 0.01 && $model->status !== 'PAID'): ?>
            <div class="block block-rounded shadow-sm border-top border-5 border-success mb-3">
                <div class="block-header bg-body-light">
                    <h3 class="block-title fw-bold text-success"><i class="fa fa-cash-register me-2"></i> Receive Payment</h3>
                </div>
                <div class="block-content block-content-full">
                    <?php $form = ActiveForm::begin(['action' => ['payment', 'id' => $model->bill_id, 'return_client' => $returnClientId]]); ?>

                    <div class="mb-3">
                        <label class="form-label text-muted fs-sm text-uppercase">Amount to Pay (in KES)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white fw-bold border-success">KES</span>
                            <?= $form->field($paymentModel, 'amount', ['options' => ['tag' => false]])->textInput([
                                'type' => 'number', 'step' => '0.01', 'max' => $model->balance, 'value' => $model->balance, 
                                'class' => 'form-control fw-bold border-success'
                            ])->label(false) ?>
                        </div>
                        <?php if ($isUsd): ?>
                            <div class="form-text text-success fw-medium fs-sm mt-1">
                                <i class="fa fa-info-circle me-1"></i> Cashbook entries are recorded in KES. To clear the <?= $sym . number_format($balance, 2) ?> balance, the required payment is <strong>KES <?= number_format($model->balance, 2) ?></strong>.
                            </div>
                        <?php endif; ?>
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
            <?php if ($balance <= 0.01 || $model->status === 'CREDIT'): ?>
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

<?php if (!empty($model->reversals)): ?>
<div class="block block-rounded border border-danger shadow-sm mt-4">
    <div class="block-header bg-danger-light">
        <h3 class="block-title text-danger fw-bold"><i class="fa fa-shield-alt me-2"></i> Audit Trail: Reversals & Modifications</h3>
    </div>
    <div class="block-content p-0">
        <table class="table table-sm table-striped mb-0 fs-sm">
            <thead class="bg-body-light text-muted">
                <tr>
                    <th class="ps-3">Date</th>
                    <th>Voided Invoice #</th>
                    <th>Original Total</th>
                    <th>Reason for Reversal</th>
                    <th>Authorized By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($model->reversals as $rev): ?>
                    <tr>
                        <td class="ps-3"><?= Yii::$app->formatter->asDatetime($rev->created_at) ?></td>
                        <td class="fw-bold text-decoration-line-through text-muted"><?= Html::encode($rev->old_invoice_number) ?></td>
                        <td class="font-monospace fw-bold">
                            <?php 
                                $dispAmt = ($rev->old_currency === 'USD') ? ($rev->old_grand_total / $exRate) : $rev->old_grand_total;
                                $dispSym = ($rev->old_currency === 'USD') ? '$' : 'KES ';
                                echo $dispSym . number_format($dispAmt, 2);
                            ?>
                        </td>
                        <td class="text-danger fst-italic"><?= Html::encode($rev->reversal_reason) ?></td>
                        <td><span class="badge bg-dark"><?= Html::encode($rev->reverser->username ?? 'Admin') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modal-reverse" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['action' => ['/dashboard/billing-reversal/process', 'id' => $model->bill_id]]); ?>
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="fa fa-exclamation-triangle me-1"></i> Reverse & Rebill Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger py-2 fs-sm mb-3">
                    <strong>Warning:</strong> This container has already left the yard. Reversing this invoice will mark the old document as voided, generate a new invoice number, and unlock the billing rates. This action is permanently logged in the audit trail.
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark fw-bold">Reason for Reversal <span class="text-danger">*</span></label>
                    <textarea name="reversal_reason" class="form-control form-control-alt border-danger" rows="3" placeholder="e.g., Forgot to bill lift charges, Client requested discount..." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-body-light">
                <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger fw-bold"><i class="fa fa-undo me-1"></i> Confirm Reversal</button>
            </div>
            <?php ActiveForm::end(); ?>
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
                <div class="alert alert-info py-2 fs-sm">Maximum discount allowed: <strong><?= number_format($subTotal, 2) ?> <?= $curr ?></strong></div>
                <?php 
                    $formDiscount = $isUsd ? round($model->discount_amount / $exRate, 2) : $model->discount_amount;
                    $model->discount_amount = $formDiscount; 
                ?>
                <?= $form->field($model, 'discount_amount')->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control form-control-lg'])->label("Amount to Waive ({$curr})") ?>
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
                <?php 
                    $formRate = $isUsd ? round($model->tariff_rate / $exRate, 2) : $model->tariff_rate;
                    $model->tariff_rate = $formRate; 
                ?>
                <?= $form->field($model, 'tariff_rate')->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control form-control-lg'])->label("Daily Rate ({$curr})") ?>
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