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
?>

<div class="block block-rounded content-card mb-4 border-start border-5 border-<?= $badgeColor ?> shadow-sm">
    <div class="block-content block-content-full py-3">
        <div class="row align-items-center">

            <div class="col-md-4 border-end">
                <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Container</div>
                <div class="fs-3 fw-bold text-dark d-flex align-items-center">
                    <i class="fa fa-box text-secondary me-2"></i><?= $visit->container_number ?>
                </div>
                <div class="fs-sm text-muted">
                    <span class="badge bg-secondary"><?= $visit->containerType->iso_code ?? 'Type N/A' ?></span>
                    <span class="ms-2"><i class="fa fa-ticket-alt me-1"></i> <?= $visit->ticket_no_in ?></span>
                </div>
            </div>

            <div class="col-md-5 border-end ps-md-4">
                <div class="row">
                    <div class="col-6">
                        <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Entry Time</div>
                        <div class="fw-bold text-dark fs-5">
                            <?= $dateIn ?>
                        </div>
                        <div class="fs-sm text-muted">
                            <i class="fa fa-clock me-1"></i> <?= $timeIn ?>
                        </div>
                    </div>
                    <div class="col-6 text-center border-start">
                        <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Duration</div>
                        <div class="fs-3 fw-bold text-primary">
                            <?= $model->storage_days ?> <small class="fs-sm text-muted fw-normal">Days</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 text-center text-md-end ps-md-4">
                <div class="fs-xs text-muted text-uppercase fw-bold mb-1">Billing Status</div>
                <span class="badge bg-<?= $badgeColor ?> fs-5 px-3 py-2 rounded-pill">
                    <?= $model->status ?>
                </span>
                <div class="fs-xs text-muted mt-2">
                    <?= $visit->containerOwner->owner_name ?? $visit->truck_owner_name_in ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="block block-rounded content-card h-100">
            <div class="block-header block-header-default bg-body-light">
                <h3 class="block-title fw-bold"><i class="fa fa-file-invoice-dollar me-2 text-muted"></i> Invoice Details</h3>
            </div>

            <div class="block-content p-0">
                <table class="table table-vcenter table-borderless mb-0">
                    <thead class="bg-body-light border-bottom">
                        <tr class="text-uppercase fs-xs text-muted">
                            <th class="ps-4">Description</th>
                            <th class="text-center">Action / Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end pe-4">Total</th>
                        </tr>
                    </thead>
                    <tbody>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Storage Charges</div>
                                <div class="fs-xs text-muted">Daily Rate (x <?= $model->storage_days ?> days)</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-light text-primary"><?= $model->storage_days ?> Days</span>
                            </td>
                            <td class="text-end text-muted">
                                <?= number_format($model->tariff_rate, 2) ?>
                                <?php if ($model->status !== 'PAID'): ?>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modal-rate" class="fs-xs text-primary ms-1" title="Edit Rate">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold pe-4"><?= number_format($storage, 2) ?></td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Lift Off (Gate In)</div>
                                <div class="fs-xs text-muted">Offloading from truck</div>
                            </td>
                            <td class="text-center">
                                <?php if ($model->status !== 'PAID'): ?>
                                    <?php if ($hasLiftOff): ?>
                                        <?= Html::a('<i class="fa fa-times me-1"></i> Remove', ['toggle-lift-off', 'id' => $model->bill_id], [
                                            'data-method' => 'post',
                                            'data-params' => ['action' => 'remove'],
                                            'class' => 'btn btn-xs btn-alt-danger',
                                            'title' => 'Remove Charge'
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fa fa-plus me-1"></i> Add', ['toggle-lift-off', 'id' => $model->bill_id], [
                                            'data-method' => 'post',
                                            'data-params' => ['action' => 'add'],
                                            'class' => 'btn btn-xs btn-alt-primary'
                                        ]) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $hasLiftOff ? 'Applied' : 'N/A' ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-muted"><?= number_format($settingLiftOff, 2) ?></td>
                            <td class="text-end fw-bold pe-4">
                                <?= ($hasLiftOff) ? number_format($settingLiftOff, 2) : '0.00' ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">Lift On (Gate Out)</div>
                                <div class="fs-xs text-muted">Loading onto truck</div>
                            </td>
                            <td class="text-center">
                                <?php if ($model->status !== 'PAID'): ?>
                                    <?php if ($hasLiftOn): ?>
                                        <?= Html::a('<i class="fa fa-times me-1"></i> Remove', ['toggle-lift-on', 'id' => $model->bill_id], [
                                            'data-method' => 'post',
                                            'data-params' => ['action' => 'remove'],
                                            'class' => 'btn btn-xs btn-alt-danger',
                                            'title' => 'Remove Charge'
                                        ]) ?>
                                    <?php else: ?>
                                        <?= Html::a('<i class="fa fa-plus me-1"></i> Add', ['toggle-lift-on', 'id' => $model->bill_id], [
                                            'data-method' => 'post',
                                            'data-params' => ['action' => 'add'],
                                            'class' => 'btn btn-xs btn-alt-primary'
                                        ]) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $hasLiftOn ? 'Applied' : 'N/A' ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-muted"><?= number_format($settingLiftOn, 2) ?></td>
                            <td class="text-end fw-bold pe-4">
                                <?= ($hasLiftOn) ? number_format($settingLiftOn, 2) : '0.00' ?>
                            </td>
                        </tr>

                        <?php if ($repair > 0): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">Repair Charges</div>
                                    <div class="fs-xs text-muted">Survey Damages</div>
                                </td>
                                <td class="text-center text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end fw-bold pe-4"><?= number_format($repair, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                    <tfoot class="border-top bg-body-light">
                        <tr>
                            <td colspan="3" class="text-end text-muted fw-bold pt-3">Subtotal</td>
                            <td class="text-end fw-bold pt-3 pe-4"><?= number_format($subTotal, 2) ?></td>
                        </tr>

                        <tr>
                            <td colspan="3" class="text-end">
                                <?php if ($discount > 0): ?>
                                    <span class="text-danger fw-bold">
                                        <i class="fa fa-minus-circle me-1"></i> Less: Discount
                                    </span>
                                    <?php if ($model->status !== 'PAID'): ?>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modal-discount" class="fs-xs text-primary ms-2">(Edit)</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if ($model->status !== 'PAID'): ?>
                                        <a href="#" class="btn btn-sm btn-alt-secondary text-primary" data-bs-toggle="modal" data-bs-target="#modal-discount">
                                            <i class="fa fa-tag me-1"></i> Add Discount
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-danger pe-4">
                                <?= $discount > 0 ? '-' . number_format($discount, 2) : '' ?>
                            </td>
                        </tr>

                        <tr class="bg-primary-dark-op text-white">
                            <td colspan="3" class="text-end fs-5 fw-bold text-uppercase py-3">Grand Total</td>
                            <td class="text-end fs-4 fw-bold py-3 pe-4"><?= number_format($grandTotal, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="bg-white p-4">
                    <h6 class="text-uppercase text-muted fs-xs fw-bold border-bottom pb-2 mb-3">Payment History</h6>
                    <table class="table table-striped table-sm mb-0">
                        <tbody>
                            <?php if (empty($model->payments)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted fst-italic py-2">No payments received yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($model->payments as $payment): ?>
                                    <tr>
                                        <td class="fs-sm"><i class="fa fa-calendar me-2 text-muted"></i><?= Yii::$app->formatter->asDate($payment->transaction_date) ?></td>
                                        <td class="fs-sm text-center">
                                            <span class="badge bg-gray-light text-dark"><?= $payment->method ?></span>
                                            <small class="text-muted ms-1"><?= $payment->reference ?></small>
                                        </td>
                                        <td class="text-end fw-bold text-success"><?= number_format($payment->amount, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr>
                                <td colspan="2" class="text-end fw-bold">Balance Due:</td>
                                <td class="text-end fw-bold text-danger"><?= number_format($model->balance, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">

        <?php if ($model->balance > 0.01 && $model->status !== 'PAID'): ?>
            <div class="block block-rounded content-card border-top border-5 border-success mb-3 shadow-sm">
                <div class="block-header bg-body-light">
                    <h3 class="block-title text-success"><i class="fa fa-cash-register me-2"></i> Record Payment</h3>
                </div>
                <div class="block-content block-content-full">
                    <?php $form = ActiveForm::begin(['action' => ['payment', 'id' => $model->bill_id]]); ?>

                    <div class="mb-3">
                        <label class="form-label text-muted">Amount to Pay</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white fw-bold">KES</span>
                            <?= $form->field($paymentModel, 'amount', ['options' => ['tag' => false]])->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'max' => $model->balance,
                                'value' => $model->balance,
                                'class' => 'form-control fw-bold'
                            ])->label(false) ?>
                        </div>
                        <div class="form-text text-end">Max: <?= number_format($model->balance, 2) ?></div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <?= $form->field($paymentModel, 'transaction_date')->input('date', ['class' => 'form-control form-control-sm']) ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($paymentModel, 'method')->dropDownList(
                                ['CASH' => 'Cash', 'MPESA' => 'M-Pesa', 'BANK' => 'Bank', 'CHEQUE' => 'Cheque'],
                                ['class' => 'form-select form-select-sm']
                            ) ?>
                        </div>
                    </div>

                    <?= $form->field($paymentModel, 'reference')->textInput(['placeholder' => 'Ref / Cheque No']) ?>

                    <button type="submit" class="btn btn-success w-100 mt-4 py-2 fw-bold">
                        <i class="fa fa-check me-1"></i> Submit Payment
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($model->status === 'PAID'): ?>
            <div class="block block-rounded content-card bg-success-light mb-3">
                <div class="block-content block-content-full text-center py-5">
                    <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                    <h3 class="text-success fw-bold mb-1">Fully Paid</h3>
                    <p class="text-muted mb-0">This container is cleared for release.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($model->status !== 'PAID' && $model->status !== 'CREDIT'): ?>
            <div class="block block-rounded content-card border-top border-5 border-warning mb-3">
                <div class="block-header bg-body-light">
                    <h3 class="block-title text-warning-dark"><i class="fa fa-file-contract me-2"></i> Credit Exit</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="alert alert-warning fs-xs py-2 mb-3">
                        <i class="fa fa-exclamation-triangle me-1"></i> Requires Supervisor Agreement & ATL Number
                    </div>
                    <?php $form = ActiveForm::begin([
                        'action' => ['authorize-credit', 'id' => $model->bill_id],
                        'options' => ['enctype' => 'multipart/form-data']
                    ]); ?>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <?= $form->field($model, 'authorized_by')->textInput([
                                'placeholder' => 'Supervisor',
                                'class' => 'form-control form-control-alt'
                            ])->label('Authorized By') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'atl_number')->textInput([
                                'placeholder' => 'ATL-001',
                                'class' => 'form-control form-control-alt fw-bold',
                                'required' => true
                            ])->label('ATL No.') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'agreement_file')->fileInput(['required' => true, 'class' => 'form-control'])->label('Upload Signed Agreement') ?>
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-bold">
                        <i class="fa fa-check-double me-1"></i> Authorize & Release
                    </button>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>

        <?php elseif ($model->status === 'CREDIT'): ?>
            <div class="block block-rounded content-card bg-info-light mb-3">
                <div class="block-content block-content-full">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-info fw-bold mb-0"><i class="fa fa-info-circle me-1"></i> Authorized Credit Exit</h5>
                        <button type="button" class="btn btn-sm btn-alt-info bg-white" data-bs-toggle="modal" data-bs-target="#modal-edit-credit">
                            <i class="fa fa-pen me-1"></i> Edit Details
                        </button>
                    </div>

                    <div class="row g-2 fs-sm mb-3">
                        <div class="col-6">
                            <div class="text-muted text-uppercase fs-xs">Supervisor</div>
                            <div class="fw-bold text-dark"><?= Html::encode($model->authorized_by) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted text-uppercase fs-xs">ATL Number</div>
                            <div class="fw-bold text-dark"><?= Html::encode($model->atl_number) ?></div>
                        </div>
                    </div>

                    <?php if ($model->credit_agreement_path): ?>
                        <a href="<?= Yii::getAlias('@web') . '/' . $model->credit_agreement_path ?>" target="_blank" class="btn btn-sm btn-info w-100">
                            <i class="fa fa-file-pdf me-1"></i> View Signed Agreement
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-grid mt-4">
            <?= Html::a(
                'Print Invoice',
                ['/dashboard/billing/generate-invoice', 'id' => $model->bill_id],
                ['class' => 'btn btn-alt-primary btn-lg']
            ) ?>
        </div>

        <?php if ($model->balance <= 0.01 || $model->status === 'CREDIT'): ?>
            <div class="d-grid mt-2">
                <?= Html::a('<i class="fa fa-arrow-right me-2"></i> Proceed to Gate OUT', ['/dashboard/visit/out-index'], ['class' => 'btn btn-alt-secondary btn-lg']) ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<div class="modal fade" id="modal-discount" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['action' => ['update-discount', 'id' => $model->bill_id]]); ?>
            <div class="block block-rounded shadow-none mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Apply Discount / Waiver</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="block-content fs-sm py-4">
                    <div class="alert alert-info py-2">
                        <small>Current Bill Total: <strong>KES <?= number_format($subTotal, 2) ?></strong></small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Discount Amount</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">KES</span>
                            <?= $form->field($model, 'discount_amount', ['options' => ['tag' => false]])->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'class' => 'form-control',
                                'max' => $subTotal
                            ])->label(false) ?>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full block-content-sm text-end border-top bg-body-light">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Discount</button>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-rate" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <?php $form = ActiveForm::begin(['action' => ['update-rate', 'id' => $model->bill_id]]); ?>
            <div class="block block-rounded shadow-none mb-0">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Adjust Daily Rate</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="block-content fs-sm py-4">
                    <div class="alert alert-warning py-2 mb-3">
                        <small><i class="fa fa-info-circle me-1"></i> Changing the rate will recalculate the entire storage bill.</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Daily Tariff Rate</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">KES</span>
                            <?= $form->field($model, 'tariff_rate', ['options' => ['tag' => false]])->textInput([
                                'type' => 'number',
                                'step' => '0.01',
                                'class' => 'form-control',
                            ])->label(false) ?>
                        </div>
                    </div>
                </div>
                <div class="block-content block-content-full block-content-sm text-end border-top bg-body-light">
                    <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Rate</button>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php if ($model->status === 'CREDIT'): ?>
    <div class="modal fade" id="modal-edit-credit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <?php $form = ActiveForm::begin(['action' => ['update-credit-details', 'id' => $model->bill_id]]); ?>
                <div class="block block-rounded shadow-none mb-0">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Correct Authorization Details</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="block-content fs-sm py-4">
                        <div class="alert alert-warning py-2 mb-3">
                            <small><i class="fa fa-exclamation-triangle me-1"></i> Use this to fix typos in the Authorization number or Supervisor name.</small>
                        </div>

                        <div class="mb-3">
                            <?= $form->field($model, 'authorized_by')->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Supervisor Name'
                            ])->label('Authorized By') ?>
                        </div>

                        <div class="mb-3">
                            <?= $form->field($model, 'atl_number')->textInput([
                                'class' => 'form-control fw-bold',
                                'placeholder' => 'ATL-XXX'
                            ])->label('ATL Number') ?>
                        </div>
                    </div>
                    <div class="block-content block-content-full block-content-sm text-end border-top bg-body-light">
                        <button type="button" class="btn btn-alt-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
<?php endif; ?>